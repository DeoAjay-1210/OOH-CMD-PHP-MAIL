<?php
/**
 * ✅ NEW — "cmdapprovalcancel" mail body (reply to the CMD approval mail).
 * Same design as the CMD / New Bill approval mails:
 *  1. Banner + intro (payment cancelled, do not disburse)
 *  2. Cancelled bill details (bill no, month, media, cancelled by / on)
 *  3. Cancelled amount (amount, GST, total)
 *  4. Notes (reason)
 *  5. Footer
 * An empty value is not shown.
 */
$payload = $payload ?? [];
$reason  = trim((string)($payload['reason'] ?? ''));

$inr = function ($v) {
    $n = (float)$v;
    $neg = $n < 0;
    $n = round(abs($n), 2);
    $whole = (string)floor($n);
    $dec = round($n - floor($n), 2);
    // Indian grouping: 38,000 / 1,23,456
    $last3 = substr($whole, -3);
    $rest = substr($whole, 0, -3);
    if ($rest !== '' && $rest !== false) {
        $rest = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $rest);
        $whole = $rest . ',' . $last3;
    }
    $out = $whole . ($dec > 0 ? substr(number_format($dec, 2, '.', ''), 1) : '');
    return ($neg ? '-' : '') . '&#8377; ' . $out;
};

$billNo     = trim((string)($payload['bill_no'] ?? ''));
$month      = trim((string)($payload['month'] ?? ''));
$mediaCode  = trim((string)($payload['media_code'] ?? ''));
$mediaName  = trim((string)($payload['media_name'] ?? ''));
$amount     = (float)($payload['amount'] ?? 0);
$gstAmount  = (float)($payload['gst_amount'] ?? 0);
$by         = trim((string)($payload['cancelled_by'] ?? ''));
$byRole     = trim((string)($payload['cancelled_by_role_name'] ?? ''));
$at         = trim((string)($payload['cancelled_at'] ?? ''));

$byText = $by . ($byRole !== '' ? ($by !== '' ? " ($byRole)" : $byRole) : '');

// detail cells (label, value) — empty ones skipped, laid out 2 per row
$cells = [];
if ($billNo !== '')    $cells[] = ['Bill No', '<span class="no-auto-link" style="color:#df1128;">' . htmlspecialchars($billNo) . '</span>'];
if ($month !== '')     $cells[] = ['Month', htmlspecialchars($month)];
if ($mediaCode !== '') $cells[] = ['Media Code', '<span class="no-auto-link">' . htmlspecialchars($mediaCode) . '</span>'];
if ($mediaName !== '') $cells[] = ['Media Name', htmlspecialchars($mediaName)];
if ($byText !== '')    $cells[] = ['Cancelled By', htmlspecialchars($byText)];
if ($at !== '')        $cells[] = ['Cancelled On', htmlspecialchars($at)];
$cellRows = array_chunk($cells, 2);

$SECTION_CARD = 'width:100%; margin:0 0 18px 0; border:1px solid #eadfe0; border-radius:12px; overflow:hidden; background:#ffffff;';
$SECTION_HEAD = 'padding:13px 16px; border-bottom:1px solid #eadfe0; background:#ffffff; color:#df1128; font-size:14px; line-height:1.2; font-weight:800; letter-spacing:0.35px; text-transform:uppercase;';
$BADGE        = 'display:inline-block; width:30px; height:30px; line-height:30px; margin-right:10px; border-radius:8px; background:#e2182d; color:#ffffff; text-align:center; font-size:11px; font-weight:800; vertical-align:middle;';
$PILL         = 'float:right; margin-top:4px; padding:4px 10px; border-radius:20px; background:#fff0f2; border:1px solid #f3c2c9; color:#b32032; font-size:10px; font-weight:800; letter-spacing:0.5px;';
$INFO_LABEL   = 'margin:0 0 5px 0; color:#797980; font-size:10px; line-height:1.2; font-weight:700; letter-spacing:0.45px; text-transform:uppercase;';
$INFO_VALUE   = 'margin:0; color:#17171a; font-size:14px; line-height:1.45; font-weight:700; word-break:break-word;';
$RENT_LABEL   = 'width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;';
$RENT_VALUE   = 'padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;';
$TOTAL_LABEL  = 'padding:13px 14px; border-bottom:0; background:#e2182d; color:#ffffff; text-align:left; font-size:14px; font-weight:800;';
$TOTAL_VALUE  = 'padding:13px 14px; border-bottom:0; background:#e2182d; color:#ffffff; text-align:right; font-size:14px; font-weight:800; white-space:nowrap;';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CMD Payment Cancelled</title>
<style>
  a, a[x-apple-data-detectors], .no-auto-link, .no-auto-link a {
    color: inherit !important;
    text-decoration: none !important;
    font-size: inherit !important;
    font-family: inherit !important;
    font-weight: inherit !important;
    line-height: inherit !important;
  }
  * { box-sizing: border-box; }
  body { margin: 0; padding: 28px 12px; background: #eef0f2; font-family: "Segoe UI", Arial, Helvetica, sans-serif; color: #202024; }
  table { border-spacing: 0; }
  @media only screen and (max-width: 640px) {
    body { padding: 0; background: #ffffff; }
    .email-shell { border-radius: 0 !important; border: 0 !important; }
    .main-content { padding: 18px 14px 4px 14px !important; }
    .info-grid td { display: block !important; width: 100% !important; border-right: 0 !important; }
    .footer-table td { display: block !important; width: 100% !important; text-align: left !important; white-space: normal !important; padding: 10px 14px !important; }
  }
</style>
</head>
<body style="margin:0; padding:28px 12px; background:#eef0f2; font-family:'Segoe UI', Arial, Helvetica, sans-serif; color:#202024;">
<div class="email-shell" style="width:100%; max-width:920px; margin:0 auto; background:#ffffff; border:1px solid #e2e4e7; border-radius:14px; overflow:hidden; box-shadow:0 8px 30px rgba(28,28,32,0.10);">

  <!-- BANNER -->
  <div>
    <img
      src="https://adinndigital.com/api/outdoormedia/templates/image/CMD_Payment_Cancelled.png"
      alt="ADINN CMD Payment Cancelled"
      style="width:100%; height:auto; display:block; border:0;"
    >
  </div>

  <div class="main-content" style="padding:24px 28px 8px 28px;">

    <!-- INTRODUCTION -->
    <h2 style="margin:0 0 6px 0; font-size:18px; line-height:1.3; color:#17171a; font-weight:700;">Hello Team,</h2>
    <p style="margin:0 0 20px 0; font-size:13px; line-height:1.65; color:#63636a;">
      The CMD approved payment below has been <strong style="color:#df1128;">cancelled</strong>.
      </p>

    <!-- CANCELLED BILL DETAILS -->
    <?php if (!empty($cellRows)): ?>
    <div class="section-card" style="<?= $SECTION_CARD ?>">
      <div class="section-heading" style="<?= $SECTION_HEAD ?>">
        <span style="<?= $PILL ?>">CANCELLED</span>
        <span class="heading-badge" style="<?= $BADGE ?>">CB</span>
        Cancelled Bill Details
      </div>
      <table class="info-grid" role="presentation" style="width:100%; border-collapse:collapse; table-layout:fixed;">
        <?php foreach ($cellRows as $ri => $pair): $lastRow = $ri === count($cellRows) - 1; ?>
        <tr>
          <?php foreach ($pair as $ci => $cell): ?>
          <td <?= count($pair) === 1 ? 'colspan="2"' : '' ?> style="width:50%; padding:15px 16px; <?= $ci === 0 && count($pair) === 2 ? 'border-right:1px solid #eadfe0;' : '' ?> <?= $lastRow ? '' : 'border-bottom:1px solid #eadfe0;' ?> vertical-align:middle;">
            <div style="<?= $INFO_LABEL ?>"><?= htmlspecialchars($cell[0]) ?></div>
            <div style="<?= $INFO_VALUE ?>"><?= $cell[1] ?></div>
          </td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
    <?php endif; ?>

    <!-- CANCELLED AMOUNT -->
    <?php if ($amount > 0): ?>
    <div class="section-card" style="<?= $SECTION_CARD ?>">
      <div class="section-heading" style="<?= $SECTION_HEAD ?>">
        <span class="heading-badge" style="<?= $BADGE ?>">CA</span>
        Cancelled Amount
      </div>
      <table role="presentation" style="width:100%; border-collapse:collapse;">
        <tr>
          <td style="<?= $RENT_LABEL ?>">Amount</td>
          <td style="<?= $RENT_VALUE ?>"><?= $inr($amount) ?></td>
        </tr>
        <?php if ($gstAmount > 0): ?>
        <tr>
          <td style="<?= $RENT_LABEL ?>">GST</td>
          <td style="<?= $RENT_VALUE ?>"><?= $inr($gstAmount) ?></td>
        </tr>
        <?php endif; ?>
        <tr>
          <td style="<?= $TOTAL_LABEL ?>">Total Cancelled</td>
          <td style="<?= $TOTAL_VALUE ?>"><?= $inr($amount + $gstAmount) ?></td>
        </tr>
      </table>
    </div>
    <?php endif; ?>

    <!-- NOTES -->
    <?php if ($reason !== ''): ?>
    <div class="notes-box" style="margin:0 0 18px 0; padding:13px 15px; border:1px solid #f0d6da; border-left:4px solid #e2182d; border-radius:8px; background:#fff8f8;">
      <div style="margin:0 0 6px 0; color:#202024; font-size:12px; line-height:1.3; font-weight:800;">Notes</div>
      <p style="margin:0; color:#3a3a40; font-size:12px; line-height:1.6;"><strong>Reason:</strong> <?= nl2br(htmlspecialchars($reason)) ?></p>
    </div>
    <?php endif; ?>

  </div>

  <!-- FOOTER -->
  <table class="footer-table" role="presentation" style="width:100%; background:#f3f4f5; border-collapse:collapse;">
    <tr>
      <td style="padding:18px 28px; border:0; background:#f3f4f5; color:#56565c; font-size:10px; line-height:1.55; vertical-align:middle;">
        This is an automated email from ADINN Rental Management System.<br>
        Kindly review and verify the details.
      </td>
      <td style="padding:18px 28px; border:0; background:#f3f4f5; color:#2f2f34; font-size:10px; line-height:1.55; vertical-align:middle; text-align:right; white-space:nowrap; font-weight:700;">
        &copy; <?= date('Y') ?> Adinn Advertising Services Ltd. All Rights Reserved.
      </td>
    </tr>
  </table>

</div>
</body>
</html>
