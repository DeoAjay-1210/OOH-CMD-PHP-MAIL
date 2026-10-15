<?php

// Prevent raw HTML error output from corrupting JSON responses
ini_set('display_errors', 0);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Only POST method is allowed"]);
    exit;
}

// Load PHPMailer Autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$rawInput = file_get_contents("php://input");
$data = !empty($_POST) ? $_POST : json_decode($rawInput, true);

if (empty($data)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid or empty JSON payload"]);
    exit;
}

try {
    $mailType = $data['mailtype'] ?? 'forgotpin';
    if ($mailType !== 'forgotpin') {
        throw new InvalidArgumentException("Invalid mailtype. Expected 'forgotpin'");
    }

    if (empty($data['to'])) {
        throw new InvalidArgumentException("'to' recipient email address is required");
    }

    $toAddresses = is_array($data['to']) ? $data['to'] : [$data['to']];
    $ccAddresses = isset($data['cc']) ? (is_array($data['cc']) ? $data['cc'] : [$data['cc']]) : [];

    $pinData = $data['data'] ?? [];
    if (!is_array($pinData)) {
        throw new InvalidArgumentException("'data' object is required in payload");
    }

    $userName = $pinData['userName'] ?? 'User';
    $email    = $pinData['email'] ?? ($toAddresses[0] ?? '');
    $pin      = $pinData['pin'] ?? '';

    if (empty($pin)) {
        throw new InvalidArgumentException("'pin' is required inside data object");
    }

    $templatePath = __DIR__ . '/templates/forgotPinTemplate.php';
    if (!file_exists($templatePath)) {
        throw new RuntimeException("Forgot PIN email template file not found");
    }

    // Render HTML Template
    ob_start();
    include $templatePath;
    $body = ob_get_clean();

    // Exact required Subject line
    $subject = 'Rental OOH - Forgot Your PIN?';
    $fromEmail = 'noreply@adinndigital.com';
    $fromName  = 'Rental OOH';

    // Unique Message-ID, Entity-Ref-ID, and Auto-Submitted headers for transactional email handling
    $uniqHash        = md5(uniqid(microtime(true), true));
    $uniqueMessageId = "<forgotpin-{$uniqHash}@adinndigital.com>";
    $uniqueRefId     = "PIN-" . time() . "-" . mt_rand(10000, 99999);

    $mailSent = false;
    $errorMessage = '';

    // Strategy 1: Attempt PHPMailer if class exists
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
            $mail->Host       = 'localhost';
            $mail->SMTPAuth   = false;
            $mail->Port       = 25;
            $mail->MessageID  = $uniqueMessageId;

            // Transactional un-threading headers for mail clients
            $mail->addCustomHeader('X-Entity-Ref-ID', $uniqueRefId);
            $mail->addCustomHeader('X-Request-ID', $uniqueRefId);
            $mail->addCustomHeader('Auto-Submitted', 'auto-generated');
            $mail->addCustomHeader('X-Auto-Response-Suppress', 'All');

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer'       => false,
                    'verify_peer_name'  => false,
                    'allow_self_signed' => true
                ]
            ];

            $mail->setFrom($fromEmail, $fromName);
            $mail->isHTML(true);

            foreach ($toAddresses as $toEmail) {
                if (!empty(trim($toEmail))) {
                    $mail->addAddress(trim($toEmail));
                }
            }

            foreach ($ccAddresses as $ccEmail) {
                if (!empty(trim($ccEmail))) {
                    $mail->addCC(trim($ccEmail));
                }
            }

            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mailSent = $mail->send();
        } catch (Exception $e) {
            $errorMessage = "PHPMailer error: " . $e->getMessage();
        }
    }

    // Strategy 2: Fallback to native PHP mail() (cPanel standard sendmail / exim)
    if (!$mailSent) {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$fromName} <{$fromEmail}>\r\n";
        $headers .= "Reply-To: {$fromEmail}\r\n";
        $headers .= "Message-ID: {$uniqueMessageId}\r\n";
        $headers .= "X-Entity-Ref-ID: {$uniqueRefId}\r\n";
        $headers .= "X-Request-ID: {$uniqueRefId}\r\n";
        $headers .= "Auto-Submitted: auto-generated\r\n";
        $headers .= "X-Auto-Response-Suppress: All\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        $validTo = array_filter(array_map('trim', $toAddresses));
        $toStr   = implode(', ', $validTo);

        if (!empty($validTo)) {
            $sent = @mail($toStr, $subject, $body, $headers, "-f {$fromEmail}");
            if ($sent) {
                $mailSent = true;
            } else {
                $errorMessage .= " Native mail() send failed.";
            }
        } else {
            $errorMessage = "No valid recipient email addresses specified.";
        }
    }

    if ($mailSent) {
        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Forgot PIN email sent successfully",
            "mailCount" => count($toAddresses)
        ]);
    } else {
        throw new Exception("Email delivery failed. " . $errorMessage);
    }

} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
