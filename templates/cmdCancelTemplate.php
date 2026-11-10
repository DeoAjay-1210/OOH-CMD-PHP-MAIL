<?php
// ✅ NEW — "cmdapprovalcancel" mail body (reply to the CMD approval mail):
// the cancel message + the reason only. $payload = ['reason' => '...'].
$payload = $payload ?? [];
$reason  = trim((string)($payload['reason'] ?? ''));
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background:#ffffff; font-family:Arial, Helvetica, sans-serif; color:#17171a;">
  <div style="padding:20px 24px;">
    <p style="margin:0 0 14px 0; font-size:15px; line-height:1.5; font-weight:700; color:#df1128;">
      This amount was cancelled.
    </p>
    <?php if ($reason !== ''): ?>
    <p style="margin:0; font-size:14px; line-height:1.6; color:#2d2d31;">
      <span style="font-weight:700;">Reason:</span> <?= nl2br(htmlspecialchars($reason)) ?>
    </p>
    <?php endif; ?>
  </div>
</body>
</html>
