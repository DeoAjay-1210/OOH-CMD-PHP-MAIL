<?php
// ✅ NEW — "cmdapprovalcancel" mail body (reply to the CMD approval mail):
// the cancel message + bill no / month / media / amount / cancelled by / reason.
// A row whose value is empty is not shown.
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
    return ($neg ? '-' : '') . '₹' . $out;
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

$media = implode(' – ', array_filter([$mediaCode, $mediaName], function ($p) { return $p !== ''; }));
$amountText = $amount > 0 ? $inr($amount) . ($gstAmount > 0 ? ' + GST ' . $inr($gstAmount) : '') : '';
$byText = $by . ($byRole !== '' ? " ($byRole)" : '') . ($at !== '' ? ($by !== '' || $byRole !== '' ? ', ' : '') . $at : '');

$rows = [];
if ($billNo !== '')     $rows[] = ['Bill No', $billNo . ' (Cancelled)'];
if ($month !== '')      $rows[] = ['Month', $month];
if ($media !== '')      $rows[] = ['Media', $media];
if ($amountText !== '') $rows[] = ['Amount', $amountText];
if (trim($byText) !== '') $rows[] = ['Cancelled by', trim($byText)];
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background:#ffffff; font-family:Arial, Helvetica, sans-serif; color:#17171a;">
<div style="width:100%; max-width:920px; margin:0 auto; background:#ffffff; border:1px solid #e2e4e7; border-radius:14px; overflow:hidden;">
  <!-- ✅ NEW — cancel banner -->
  <img
    src="https://adinndigital.com/api/outdoormedia/templates/image/CMD-Payment-Canceled.png"
    alt="ADINN CMD Payment Cancelled"
    style="width:100%; height:auto; display:block; border:0;"
  >
  <div style="padding:20px 24px;">
    <p style="margin:0 0 14px 0; font-size:15px; line-height:1.5; font-weight:700; color:#df1128;">
      This amount was cancelled.
    </p>
    <table role="presentation" style="border-collapse:collapse; font-size:14px; line-height:1.6; color:#2d2d31;">
      <?php foreach ($rows as $r): ?>
      <tr>
        <td style="padding:2px 12px 2px 0; font-weight:700; white-space:nowrap; vertical-align:top;"><?= htmlspecialchars($r[0]) ?></td>
        <td style="padding:2px 6px 2px 0; vertical-align:top;">:</td>
        <td style="padding:2px 0; vertical-align:top;"><span class="no-auto-link"><?= htmlspecialchars($r[1]) ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if ($reason !== ''): ?>
      <tr>
        <td style="padding:2px 12px 2px 0; font-weight:700; white-space:nowrap; vertical-align:top;">Reason</td>
        <td style="padding:2px 6px 2px 0; vertical-align:top;">:</td>
        <td style="padding:2px 0; vertical-align:top;"><?= nl2br(htmlspecialchars($reason)) ?></td>
      </tr>
      <?php endif; ?>
    </table>
  </div>
</div>
</body>
</html>
