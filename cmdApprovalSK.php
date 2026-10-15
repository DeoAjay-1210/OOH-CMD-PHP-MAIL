<?php

// ─────────────────────────────────────────
// Dependencies
// ─────────────────────────────────────────
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}
require_once __DIR__ . '/cmdApprovalHelpers.php';

// ─────────────────────────────────────────
// Render + send one CMD approval email.
// ─────────────────────────────────────────
function sendCmdMail($payload, $subject, $toAddresses, $ccAddresses, $proofUrls, $invoiceUrls, $templatePath) {
    ob_start();
    include $templatePath;
    $body = ob_get_clean();

    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host     = 'localhost';
    $mail->SMTPAuth = false;
    $mail->Port     = 25;

    $mail->setFrom('noreply@adinndigital.com', 'CMD Approved');
    $mail->isHTML(true);

    foreach ($toAddresses as $email) {
        if (!empty(trim($email))) {
            $mail->addAddress(trim($email));
        }
    }

    foreach ($ccAddresses as $email) {
        if (!empty(trim($email))) {
            $mail->addCC(trim($email));
        }
    }

    // Attach proof-of-campaign images (fetched from their remote URLs) — attachment only,
    // not embedded in the HTML body. A failed fetch for one URL is skipped, not fatal.
    foreach ($proofUrls as $i => $imageUrl) {
        $imageUrl = trim((string)$imageUrl);
        if ($imageUrl === '') continue;
        $imageData = fetchRemoteFile($imageUrl);
        if ($imageData === null) continue;
        $parsedPath = parse_url($imageUrl, PHP_URL_PATH) ?: '';
        $baseName = basename($parsedPath) ?: 'proof-of-campaign.jpg';
        $ext = pathinfo($baseName, PATHINFO_EXTENSION) ?: 'jpg';
        $nameOnly = pathinfo($baseName, PATHINFO_FILENAME) ?: 'proof-of-campaign';
        $filename = $nameOnly . '-' . ($i + 1) . '.' . $ext;
        $mail->addStringAttachment($imageData, $filename);
    }

    // Attach GST invoice PDF(s) (mandatory whenever GST is applicable — see
    // resolveGstInvoice() in cmdApprovalHelpers.php). Same fetch-and-skip-on-failure
    // behavior as proof-of-campaign attachments above.
    foreach ($invoiceUrls as $i => $pdfUrl) {
        $pdfUrl = trim((string)$pdfUrl);
        if ($pdfUrl === '') continue;
        $pdfData = fetchRemoteFile($pdfUrl);
        if ($pdfData === null) continue;
        $filename = basename(parse_url($pdfUrl, PHP_URL_PATH) ?: '') ?: ('gst-invoice.pdf');
        $mail->addStringAttachment($pdfData, $filename, PHPMailer::ENCODING_BASE64, 'application/pdf');
    }

    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->send();
}

// Subject: "CMD Payment Approved - <Landowner Name> - <Media Code>".
// Empty parts are skipped so the subject never ends with a dangling " - ".
function buildCmdSubject(array $landOwnersRaw, string $mediaCode): string {
    $names = array_values(array_filter(array_map(
        function ($o) { return trim((string)($o['name'] ?? '')); },
        $landOwnersRaw
    )));
    $ownerPart = count($landOwnersRaw) > 1
        ? (!empty($names) ? implode(', ', $names) : 'Multiple Landlords')
        : ($names[0] ?? '');

    $parts = array_filter([$ownerPart, trim($mediaCode)], function ($p) { return $p !== ''; });
    return 'CMD Payment Approved' . ($parts ? ' - ' . implode(' - ', $parts) : '');
}

/**
 * Core function to process a CMD Approval payload and send email using existing business rules.
 * Reused by both HTTP API calls and automated CLI test runners.
 */
function processCmdApprovalRequest(array $data): array {
    if (empty($data['mailtype']) || $data['mailtype'] !== 'cmdapproval') {
        throw new InvalidArgumentException("mailtype must be 'cmdapproval'");
    }

    if (empty($data['to'])) {
        throw new InvalidArgumentException("'to' address is required");
    }

    if (empty($data['data']) || !is_array($data['data'])) {
        throw new InvalidArgumentException("'data' object is required");
    }

    $toAddresses = $data['to'] ?? [];
    $ccAddresses = $data['cc'] ?? [];
    if (is_string($toAddresses)) $toAddresses = [$toAddresses];
    if (is_string($ccAddresses)) $ccAddresses = [$ccAddresses];

    $media = $data['data'];

    $templatePath = __DIR__ . '/templates/cmdTemplateSK.php';

    if (!file_exists($templatePath)) {
        throw new RuntimeException("Template file not found");
    }

    $billingType = $media['billingType'] ?? 'single_bill';
    if ($billingType === 'seperate_bill') {
        $billingType = 'separate_bill';
    }
    $sites = $media['sites'] ?? [];

    if (empty($sites)) {
        // ── Legacy / single-site path — unchanged for existing callers. ──
        $built   = buildSingleMediaPayload($media);
        $subject = buildCmdSubject($media['landOwners'] ?? [], (string)($built['payload']['media_code'] ?? ''));
        sendCmdMail($built['payload'], $subject, $toAddresses, $ccAddresses, $built['proof_urls'], $built['invoice_urls'], $templatePath);

        return ["status" => "success", "message" => "Mail sent successfully", "mailCount" => 1];
    }

    $topLandOwners   = $media['landOwners'] ?? [];
    $isMultiLandlord = count($topLandOwners) > 1;

    if ($billingType === 'separate_bill' && !$isMultiLandlord && count($sites) > 1) {
        // ── separate_bill + 1 landlord + multiple sites: one email per site. ──
        $owner = $topLandOwners[0] ?? [];
        $sentCount = 0;

        foreach ($sites as $site) {
            $siteCode = $site['mediaCode'] ?? '';
            $scopedOwner = scopeOwnerToSite($owner, $siteCode);

            $singleSiteMedia = $site;
            $singleSiteMedia['landOwners']         = [$scopedOwner];
            $singleSiteMedia['agreement']           = $site['agreement']  ?? ($media['agreement']  ?? []);
            $singleSiteMedia['appraisal']           = $site['appraisal']  ?? ($media['appraisal']  ?? []);
            $singleSiteMedia['numberOfLandOwners']  = 1;
            $singleSiteMedia['status']              = $site['status']    ?? ($media['status']      ?? null);

            $built   = buildSingleMediaPayload($singleSiteMedia);
            $subject = buildCmdSubject([$scopedOwner], (string)($built['payload']['media_code'] ?? ''));
            sendCmdMail($built['payload'], $subject, $toAddresses, $ccAddresses, $built['proof_urls'], $built['invoice_urls'], $templatePath);
            $sentCount++;
        }

        return ["status" => "success", "message" => "$sentCount mail(s) sent successfully", "mailCount" => $sentCount];
    }

    // ── single_bill (any landlord/site count) or separate_bill + multiple  ──
    // ── landlords: ONE consolidated email with the full site breakdown.    ──
    $built = buildMultiSitePayload($sites, $topLandOwners, $media);

    $ownerNames = array_values(array_filter(array_map('trim', array_map('strval', array_column($topLandOwners, 'name')))));
    $subject = 'CMD Payment Approved - ' . ($isMultiLandlord
        ? (!empty($ownerNames) ? implode(', ', $ownerNames) : 'Multiple Landlords')
        : (reset($ownerNames) ?: 'Consolidated Bill'));

    // Append the site media code(s) to the subject, e.g. "... - 657" or "... - 657, 658".
    $siteCodes = array_filter(array_map(
        function ($s) { return trim((string)($s['mediaCode'] ?? '')); },
        $sites
    ));
    if (!empty($siteCodes)) {
        $subject .= ' - ' . implode(', ', array_unique($siteCodes));
    }

    sendCmdMail($built['payload'], $subject, $toAddresses, $ccAddresses, $built['proof_urls'], $built['invoice_urls'], $templatePath);

    return ["status" => "success", "message" => "Mail sent successfully", "mailCount" => 1];
}

// ─────────────────────────────────────────
// HTTP Direct Request Handling Bootstrap
// ─────────────────────────────────────────
if (isset($_SERVER['REQUEST_METHOD'])) {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    header("Content-Type: application/json");

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["status" => "error", "message" => "Only POST allowed"]);
        exit;
    }

    if (!empty($_POST)) {
        $data = $_POST;
    } else {
        $data = json_decode(file_get_contents("php://input"), true);
    }

    try {
        $result = processCmdApprovalRequest($data ?? []);
        echo json_encode($result);
    } catch (InvalidArgumentException $e) {
        http_response_code(422);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
    exit;
}
