<?php
/**
 * CMD Approval Request - Final Email Template
 *
 * Design order:
 *  1. Media information
 *  2. Rent details and payment schedule
 *  3. Single / multiple landlord details
 *  4. Detailed media and agreement information
 *
 * Email-safe implementation:
 *  - Table-based desktop layout
 *  - Responsive stacking below 640px
 *  - Important visual styles are duplicated inline
 *  - No emoji characters are used
 */

$payload = $payload ?? [];

// MEDIA DETAILS
$media_code        = $payload['media_code']        ?? '';
$media_name        = $payload['media_name']        ?? '';
$media_type        = $payload['media_type']        ?? '';
$state             = $payload['state']             ?? '';
$city              = $payload['city']              ?? '';
$location          = $payload['location']          ?? '';
$full_address      = $payload['full_address']      ?? '';
$width_ft          = $payload['width_ft']          ?? '';
$height_ft         = $payload['height_ft']         ?? '';
$total_sqft        = $payload['total_sqft']        ?? '';
$num_land_owners   = $payload['num_land_owners']   ?? '';
$media_status      = $payload['media_status']      ?? '';
$media_details_list = $payload['media_details_list'] ?? [];

// AGREEMENT DETAILS
$agree_start_date    = $payload['agree_start_date']    ?? '';
$agree_end_date      = $payload['agree_end_date']      ?? '';
$reminder_days       = $payload['reminder_days']       ?? '';
$advance_rent        = $payload['advance_rent']        ?? '';
$total_rental_amt    = $payload['total_rental_amt']    ?? '';
$payment_frequency   = $payload['payment_frequency']   ?? '';
$last_bill_date      = $payload['last_bill_date']      ?? '';
$current_bill_date   = $payload['current_bill_date']   ?? '';
$next_billing_date   = $payload['next_billing_date']   ?? '';
$gst_applicable      = $payload['gst_applicable']      ?? '';
$gst_applicable_flag = $payload['gst_applicable_flag'] ?? 0;
$gst_amount          = $payload['gst_amount']          ?? '';
$gst_number          = $payload['gst_number']          ?? '';
$tds_applicable      = $payload['tds_applicable']      ?? '';
$agree_status        = $payload['agree_status']        ?? '';
$is_separate_agreement  = $payload['is_separate_agreement']  ?? false;
$agreement_details_list = $payload['agreement_details_list'] ?? [];

// RENTAL PAYMENT SUMMARY
$rp_total_rental           = $payload['rp_total_rental']           ?? '';
$rp_gst_applicable         = $payload['rp_gst_applicable']         ?? '';
$rp_gst_number             = $payload['rp_gst_number']             ?? '';
$rp_payment_frequency      = $payload['rp_payment_frequency']      ?? '';
$rp_last_bill_date         = $payload['rp_last_bill_date']         ?? '';
$rp_current_bill_date      = $payload['rp_current_bill_date']      ?? '';
$rp_tds_applicable         = $payload['rp_tds_applicable']         ?? '';
$rp_gst_percentage         = $payload['rp_gst_percentage']         ?? '';
$rp_gst_amount             = $payload['rp_gst_amount']             ?? '';
$rp_tds_percentage         = $payload['rp_tds_percentage']         ?? '';
$rp_tds_amount             = $payload['rp_tds_amount']             ?? '';
$rp_net_payable            = $payload['rp_net_payable']            ?? '';
$rp_next_bill_date         = $payload['rp_next_bill_date']         ?? '';
$rp_appraisal_applicable   = $payload['rp_appraisal_applicable']   ?? '';
$rp_appraisal_type         = $payload['rp_appraisal_type']         ?? '';
$rp_appraisal_percentage   = $payload['rp_appraisal_percentage']   ?? '';
$rp_appraisal_fixed_amount = $payload['rp_appraisal_fixed_amount'] ?? '';
$rp_appraisal_frequency    = $payload['rp_appraisal_frequency']    ?? '';
$rp_appraisal_amount       = $payload['rp_appraisal_amount']       ?? '';
$rp_total_appraisal_amount = $payload['rp_total_appraisal_amount'] ?? '';
$rp_last_appraisal_date    = $payload['rp_last_appraisal_date']    ?? '';
$rp_next_appraisal_date    = $payload['rp_next_appraisal_date']    ?? '';

$is_gst_applicable            = $payload['is_gst_applicable']            ?? false;
$is_tds_applicable            = $payload['is_tds_applicable']            ?? false;
$is_appraisal_applicable      = $payload['is_appraisal_applicable']      ?? false;
$is_appraisal_percentage_type = $payload['is_appraisal_percentage_type'] ?? false;
$is_appraisal_fixed_type      = $payload['is_appraisal_fixed_type']      ?? false;
$is_appraisal_included_in_rent= $payload['is_appraisal_included_in_rent']?? false;
$gst_invoice_attached         = $payload['gst_invoice_attached']         ?? false;
$gst_invoice_missing          = $payload['gst_invoice_missing']          ?? false;

$rp_rental_base         = $payload['rp_rental_base']         ?? '';
$rp_appraisal_amt_calc  = $payload['rp_appraisal_amt_calc']  ?? '';
$rp_total_with_appraisal= $payload['rp_total_with_appraisal']?? '';
$rp_gst_amt_calc        = $payload['rp_gst_amt_calc']        ?? '';
$rp_tds_base_amount     = $payload['rp_tds_base_amount']     ?? '';
$rp_tds_amt_calc        = $payload['rp_tds_amt_calc']        ?? '';
$rp_grand_total_payable = $payload['rp_grand_total_payable'] ?? '';
$rp_gst_pct_val         = $payload['rp_gst_pct_val']         ?? '';

// LAND OWNERS
$land_owners             = $payload['land_owners']             ?? [];
$is_multiple_landowners  = $payload['is_multiple_landowners']  ?? false;
$is_gst_hold             = $payload['is_gst_hold']             ?? false;
$is_tds_hold             = $payload['is_tds_hold']             ?? false;
$multi_tds_hold_notes    = $payload['multi_tds_hold_notes']    ?? [];
$multi_tds_deducted_rows = $payload['multi_tds_deducted_rows'] ?? [];
$has_notes               = $payload['has_notes']               ?? true;

// MULTI-SITE CONSOLIDATED BILL (billingType-driven — see cmdApprovalSK.php).
// false for the legacy single-site flow; section below is unchanged for that case.
$is_multi_site        = $payload['is_multi_site']         ?? false;
$sites                = $payload['sites']                 ?? [];
$show_appraisal_column= $payload['show_appraisal_column'] ?? false;
$show_gst_column      = $payload['show_gst_column']       ?? false;
$total_rent_amount    = $payload['total_rent_amount']     ?? '';
$total_appraisal_amount = $payload['total_appraisal_amount'] ?? '';
$total_appraisal_amount_raw = $payload['total_appraisal_amount_raw'] ?? '';
$total_gst_amount     = $payload['total_gst_amount']      ?? '';
$total_tds_amount     = $payload['total_tds_amount']      ?? '';
$gst_invoice_missing_any = $payload['gst_invoice_missing_any'] ?? false;

// RETAINED LEGACY VALUES
$grand_total            = $payload['grand_total']            ?? '';
$appraisal_applicable   = $payload['appraisal_applicable']   ?? '';
$appraisal_type         = $payload['appraisal_type']         ?? '';
$appraisal_pct          = $payload['appraisal_pct']          ?? '';
$appraisal_fixed_amt    = $payload['appraisal_fixed_amt']    ?? '';
$appraisal_frequency    = $payload['appraisal_frequency']    ?? '';
$current_rent           = $payload['current_rent']           ?? '';
$appraisal_amount       = $payload['appraisal_amount']       ?? '';
$total_appraisal_amt    = $payload['total_appraisal_amt']    ?? '';
$last_appraisal_date    = $payload['last_appraisal_date']    ?? '';
$next_appraisal_date    = $payload['next_appraisal_date']    ?? '';

$media_address_display = trim((string)$full_address) !== ''
    ? $full_address
    : $location;

$raw_appraisal_num = (float)str_replace([',', '₹', ' '], '', $rp_appraisal_amount !== '' ? $rp_appraisal_amount : ($rp_appraisal_amt_calc !== '' ? $rp_appraisal_amt_calc : $rp_appraisal_fixed_amount));
if ($is_appraisal_applicable) {
    if ($raw_appraisal_num > 0) {
        $appraisal_display = '₹ ' . fmtAmount($raw_appraisal_num);
    } elseif (!empty($rp_appraisal_percentage) && (float)$rp_appraisal_percentage > 0) {
        $appraisal_display = $rp_appraisal_percentage . '%';
    } else {
        $appraisal_display = 'Yes';
    }
} else {
    $appraisal_display = 'No';
}

$landlord_count = is_array($land_owners) ? count($land_owners) : 0;

$HEADER_CELL = 'border:1px solid #eadfe0; background:#fff7f8; color:#5f5f63; font-size:11px; font-weight:700; letter-spacing:0.45px; text-transform:uppercase; padding:10px 12px; text-align:left; vertical-align:middle;';
$VALUE_CELL  = 'border:1px solid #eadfe0; background:#ffffff; color:#18181b; font-size:13px; font-weight:600; padding:10px 12px; text-align:left; vertical-align:middle;';
$TABLE_HEAD  = 'border:1px solid #eadfe0; background:#f8eef0; color:#2d2d31; font-size:11px; font-weight:700; padding:10px 10px; text-align:center; vertical-align:middle; white-space:nowrap;';
$TABLE_CELL  = 'border:1px solid #eadfe0; background:#ffffff; color:#2d2d31; font-size:11px; font-weight:500; padding:10px 10px; text-align:center; vertical-align:middle;';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>CMD Payment Approved</title>
<style>
  /* Override auto-generated blue hyperlinks from email clients (Gmail, Apple Mail, Outlook) */
  a,
  .responsive-table td a,
  .responsive-table a,
  a[x-apple-data-detectors],
  .no-auto-link,
  .no-auto-link a,
  span.no-auto-link a {
    color: inherit !important;
    text-decoration: none !important;
    font-size: inherit !important;
    font-family: inherit !important;
    font-weight: inherit !important;
    line-height: inherit !important;
  }

  * {
    box-sizing: border-box;
  }

  body {
    margin: 0;
    padding: 28px 12px;
    background: #eef0f2;
    font-family: "Segoe UI", Arial, Helvetica, sans-serif;
    color: #202024;
  }

  table {
    border-spacing: 0;
  }

  .email-shell {
    width: 100%;
    max-width: 920px;
    margin: 0 auto;
    background: #ffffff;
    border: 1px solid #e2e4e7;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 30px rgba(28, 28, 32, 0.10);
  }

  .header-banner {
    width: 100%;
    height: auto;
    display: block;
    border: 0;
  }

  .main-content {
    padding: 24px 28px 8px 28px;
  }

  .intro-title {
    margin: 0 0 6px 0;
    font-size: 18px;
    line-height: 1.3;
    color: #17171a;
    font-weight: 700;
  }

  .intro-copy {
    margin: 0 0 20px 0;
    font-size: 13px;
    line-height: 1.65;
    color: #63636a;
  }

  .section-card {
    width: 100%;
    margin: 0 0 18px 0;
    border: 1px solid #eadfe0;
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
  }

  .section-heading {
    padding: 13px 16px;
    border-bottom: 1px solid #eadfe0;
    background: #ffffff;
    color: #df1128;
    font-size: 14px;
    line-height: 1.2;
    font-weight: 800;
    letter-spacing: 0.35px;
    text-transform: uppercase;
  }

  .section-heading-red {
    padding: 12px 16px;
    background: #e2182d;
    color: #ffffff;
    font-size: 13px;
    line-height: 1.2;
    font-weight: 800;
    letter-spacing: 0.35px;
    text-transform: uppercase;
  }

  .heading-badge {
    display: inline-block;
    width: 30px;
    height: 30px;
    line-height: 30px;
    margin-right: 10px;
    border-radius: 8px;
    background: #e2182d;
    color: #ffffff;
    text-align: center;
    font-size: 11px;
    font-weight: 800;
    vertical-align: middle;
  }

  .heading-badge-light {
    display: inline-block;
    min-width: 30px;
    height: 26px;
    line-height: 26px;
    margin-right: 9px;
    padding: 0 7px;
    border-radius: 7px;
    background: rgba(255,255,255,0.18);
    color: #ffffff;
    text-align: center;
    font-size: 10px;
    font-weight: 800;
    vertical-align: middle;
  }

  .media-grid {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .media-grid td {
    width: 50%;
    padding: 15px 16px;
    border-bottom: 1px solid #eadfe0;
    vertical-align: middle;
  }

  .media-grid td:nth-child(odd) {
    border-right: 1px solid #eadfe0;
  }

  .media-grid tr:last-child td {
    border-bottom: 0;
  }

  .info-label {
    margin: 0 0 5px 0;
    color: #797980;
    font-size: 10px;
    line-height: 1.2;
    font-weight: 700;
    letter-spacing: 0.45px;
    text-transform: uppercase;
  }

  .info-value {
    margin: 0;
    color: #17171a;
    font-size: 14px;
    line-height: 1.45;
    font-weight: 700;
    word-break: break-word;
  }

  .split-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .split-column {
    width: 50%;
    padding: 0;
    vertical-align: top;
    border: 0;
  }

  .split-column:first-child {
    border-right: 1px solid #eadfe0;
  }

  .rent-table,
  .schedule-table,
  .detail-table {
    width: 100%;
    border-collapse: collapse;
  }

  .rent-table td,
  .schedule-table td {
    padding: 11px 14px;
    border-bottom: 1px solid #eee5e6;
    font-size: 12px;
    line-height: 1.35;
    vertical-align: middle;
  }

  .rent-table tr:last-child td,
  .schedule-table tr:last-child td {
    border-bottom: 0;
  }

  .rent-label,
  .schedule-label {
    width: 60%;
    color: #505057;
    text-align: left;
    font-weight: 600;
  }

  .rent-value,
  .schedule-value {
    color: #17171a;
    text-align: right;
    font-weight: 700;
    white-space: nowrap;
  }

  .rent-muted td {
    color: #77777e;
    background: #fcf9f9;
  }

  .rent-negative td {
    color: #b32032;
  }

  .rent-total td {
    padding-top: 13px;
    padding-bottom: 13px;
    background: #e2182d;
    color: #ffffff;
    font-size: 14px;
    font-weight: 800;
  }

  .hold-label {
    display: inline-block;
    margin-left: 6px;
    padding: 2px 6px;
    border-radius: 4px;
    background: #fff0f2;
    color: #b32032;
    font-size: 9px;
    font-weight: 800;
    text-decoration: none;
  }

  .tds-note-icon {
    display: inline-block;
    width: 14px;
    height: 14px;
    line-height: 14px;
    margin-right: 6px;
    border-radius: 50%;
    background: #b32032;
    color: #ffffff;
    text-align: center;
    font-size: 9px;
    font-weight: 800;
    font-style: normal;
    vertical-align: middle;
  }

  .tds-note-text {
    font-size: 10px;
    line-height: 1.4;
    color: #55555b;
    vertical-align: middle;
  }

  .stat-table {
    width: 100%;
    margin: 0 0 18px 0;
    border-collapse: separate;
    table-layout: fixed;
  }

  .stat-cell {
    padding: 0 5px;
    border: 0;
    vertical-align: top;
  }

  .stat-cell:first-child {
    padding-left: 0;
  }

  .stat-cell:last-child {
    padding-right: 0;
  }

  .stat-card {
    height: 104px;
    border: 1px solid #eadfe0;
    border-radius: 10px;
    background: #fffafa;
    text-align: center;
  }

  .stat-inner {
    width: 100%;
    height: 104px;
    border-collapse: collapse;
  }

  .stat-inner td {
    padding: 10px 9px;
    border: 0;
    vertical-align: middle;
    text-align: center;
  }

  .stat-label {
    margin: 0 0 6px 0;
    color: #77777e;
    font-size: 9px;
    line-height: 1.25;
    font-weight: 800;
    letter-spacing: 0.45px;
    text-transform: uppercase;
  }

  .stat-value {
    margin: 0;
    color: #17171a;
    font-size: 14px;
    line-height: 1.25;
    font-weight: 800;
  }

  .stat-sub {
    margin: 5px 0 0 0;
    color: #77777e;
    font-size: 9px;
    line-height: 1.4;
    font-weight: 500;
  }

  .subsection-title {
    padding: 9px 12px;
    background: #fff5f6;
    border-bottom: 1px solid #eadfe0;
    color: #b8192d;
    font-size: 10px;
    line-height: 1.3;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
  }

  .detail-table td {
    padding: 9px 11px;
    border-bottom: 1px solid #eee5e6;
    font-size: 11px;
    line-height: 1.35;
    vertical-align: middle;
  }

  .detail-table tr:last-child td {
    border-bottom: 0;
  }

  .detail-label {
    width: 48%;
    background: #fffafa;
    color: #5b5b61;
    font-weight: 700;
    text-align: left;
  }

  .detail-value {
    color: #26262a;
    font-weight: 600;
    text-align: left;
    word-break: break-word;
  }

  .owner-card {
    margin: 0 0 14px 0;
    border: 1px solid #eadfe0;
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
  }

  .owner-card:last-child {
    margin-bottom: 0;
  }

  .owner-heading {
    padding: 11px 14px;
    background: #2b2b2f;
    color: #ffffff;
    font-size: 12px;
    line-height: 1.35;
    font-weight: 800;
  }

  .owner-heading-name {
    color: #ffffff;
    font-weight: 600;
  }

  .owner-summary-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .owner-summary-column {
    width: 50%;
    padding: 0;
    border: 0;
    vertical-align: top;
  }

  .owner-summary-column:first-child {
    border-right: 1px solid #eadfe0;
  }

  .payment-highlight {
    color: #b8192d !important;
    font-weight: 800 !important;
  }

  .payment-total td {
    background: #fff3f4;
    color: #b8192d;
    font-weight: 800;
  }

  .notes-box {
    margin: 0 0 18px 0;
    padding: 13px 15px;
    border: 1px solid #f0d6da;
    border-left: 4px solid #e2182d;
    border-radius: 8px;
    background: #fff8f8;
  }

  .notes-title {
    margin: 0 0 6px 0;
    color: #202024;
    font-size: 12px;
    line-height: 1.3;
    font-weight: 800;
  }

  .notes-copy {
    margin: 0 0 4px 0;
    color: #55555b;
    font-size: 11px;
    line-height: 1.6;
  }

  .notes-copy:last-child {
    margin-bottom: 0;
  }

  .notes-hold {
    color: #b32032;
    font-weight: 800;
  }

  .closing-copy {
    margin: 4px 0 18px 0;
    color: #55555b;
    font-size: 12px;
    line-height: 1.6;
  }

  .lower-grid {
    width: 100%;
    border-collapse: separate;
    table-layout: fixed;
    margin: 0 0 20px 0;
  }

  .lower-column {
    width: 50%;
    padding: 0 6px;
    border: 0;
    vertical-align: top;
  }

  .lower-column:first-child {
    padding-left: 0;
  }

  .lower-column:last-child {
    padding-right: 0;
  }

  .lower-card {
    border: 1px solid #eadfe0;
    border-radius: 10px;
    overflow: hidden;
    background: #ffffff;
  }

  .footer-table {
    width: 100%;
    background: #f3f4f5;
    border-collapse: collapse;
  }

  .footer-table td {
    padding: 18px 28px;
    border: 0;
    background: #f3f4f5;
    color: #56565c;
    font-size: 10px;
    line-height: 1.55;
    vertical-align: middle;
  }

  .footer-brand {
    text-align: right;
    white-space: nowrap;
    font-weight: 700;
    color: #2f2f34 !important;
  }

  .brand-red {
    color: #e2182d;
  }

  .table-wrap {
    width: 100%;
    overflow-x: auto;
  }

  .mobile-label {
    display: none;
  }

  @media only screen and (max-width: 640px) {
    body {
      padding: 0;
      background: #ffffff;
    }

    .email-shell {
      border: 0;
      border-radius: 0;
      box-shadow: none;
    }

    .main-content {
      padding: 18px 12px 4px 12px !important;
    }

    .media-grid,
    .media-grid tbody,
    .media-grid tr,
    .media-grid td {
      display: block !important;
      width: 100% !important;
    }

    .media-grid td {
      border-right: 0 !important;
      border-bottom: 1px solid #eadfe0 !important;
    }

    .media-grid tr:last-child td:last-child {
      border-bottom: 0 !important;
    }

    .split-table,
    .split-table tbody,
    .split-table tr,
    .split-column {
      display: block !important;
      width: 100% !important;
    }

    .split-column:first-child {
      border-right: 0 !important;
      border-bottom: 1px solid #eadfe0 !important;
    }

    .stat-table,
    .stat-table tbody,
    .stat-table tr,
    .stat-cell {
      display: block !important;
      width: 100% !important;
    }

    .stat-cell {
      padding: 0 0 9px 0 !important;
    }

    .stat-card,
    .stat-inner {
      height: 88px !important;
    }

    .owner-summary-table,
    .owner-summary-table tbody,
    .owner-summary-table tr,
    .owner-summary-column {
      display: block !important;
      width: 100% !important;
    }

    .owner-summary-column:first-child {
      border-right: 0 !important;
      border-bottom: 1px solid #eadfe0 !important;
    }

    .lower-grid,
    .lower-grid tbody,
    .lower-grid tr,
    .lower-column {
      display: block !important;
      width: 100% !important;
    }

    .lower-column {
      padding: 0 0 12px 0 !important;
    }

    .footer-table td {
      display: block !important;
      width: 100% !important;
      padding: 14px 16px !important;
      text-align: left !important;
    }

    .footer-brand {
      padding-top: 0 !important;
    }

    .responsive-table thead {
      display: none !important;
    }

    .responsive-table,
    .responsive-table tbody,
    .responsive-table tr,
    .responsive-table td {
      display: block !important;
      width: 100% !important;
    }

    .responsive-table tr {
      margin: 0 0 10px 0 !important;
      border: 1px solid #eadfe0 !important;
      border-radius: 8px !important;
      overflow: hidden !important;
    }

    .responsive-table td {
      min-width: 0 !important;
      padding: 8px 10px !important;
      border: 0 !important;
      border-bottom: 1px solid #eadfe0 !important;
      text-align: left !important;
      white-space: normal !important;
      word-break: break-word !important;
    }

    .responsive-table td:last-child {
      border-bottom: 0 !important;
    }

    .mobile-label {
      display: inline-block !important;
      min-width: 132px;
      color: #b8192d;
      font-weight: 800;
    }
  }
</style>
</head>
<body>
<div class="email-shell" style="width:100%; max-width:920px; margin:0 auto; background:#ffffff; border:1px solid #e2e4e7; border-radius:14px; overflow:hidden;">

  <!-- HEADER -->
  <div style="width:100%; line-height:0; font-size:0;">
    <img
      class="header-banner"
      src="https://adinndigital.com/api/outdoormedia/templates/image/header-banner2.png"
      alt="ADINN CMD Payment Approved"
      style="width:100%; height:auto; display:block; border:0;"
    >
  </div>

  <div class="main-content" style="padding:24px 28px 8px 28px;">

    <!-- INTRODUCTION -->
    <h2 class="intro-title" style="margin:0 0 6px 0; font-size:18px; line-height:1.3; color:#17171a; font-weight:700;">Hello Team,</h2>
    <p class="intro-copy" style="margin:0 0 20px 0; font-size:13px; line-height:1.65; color:#63636a;">
      Rental Payment Approved – Ready for Disbursement.
    </p>

    <?php if ($is_multi_site): ?>
    <!-- MULTI-SITE: SITE-WISE BILL BREAKDOWN (GST columns only for a single landlord — GST is
         never payable across multiple landlords, enforced in cmdApprovalSK.php) -->
    <div class="section-card" style="width:100%; margin:0 0 18px 0; border:1px solid #eadfe0; border-radius:12px; overflow:hidden; background:#ffffff;">
      <div class="section-heading" style="padding:13px 16px; border-bottom:1px solid #eadfe0; background:#ffffff; color:#df1128; font-size:14px; line-height:1.2; font-weight:800; letter-spacing:0.35px; text-transform:uppercase;">
        <span class="heading-badge" style="display:inline-block; width:30px; height:30px; line-height:30px; margin-right:10px; border-radius:8px; background:#e2182d; color:#ffffff; text-align:center; font-size:11px; font-weight:800; vertical-align:middle;">SB</span>
        Site-wise Bill Breakdown
      </div>

      <div class="table-wrap" style="width:100%; overflow-x:auto;">
        <table class="responsive-table" style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="<?= $TABLE_HEAD ?>">Site Code</th>
              <th style="<?= $TABLE_HEAD ?>">Site / Location</th>
              <th style="<?= $TABLE_HEAD ?>">Base Rent</th>
              <?php if ($show_appraisal_column): ?>
              <th style="<?= $TABLE_HEAD ?>">Appraisal</th>
              <?php endif; ?>
              <!-- <?php if ($show_gst_column): ?>
              <th style="<?= $TABLE_HEAD ?>">GST Amount</th>
              <?php endif; ?> -->
              <th style="<?= $TABLE_HEAD ?>">Net Payable</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($sites as $site): ?>
            <tr>
              <td style="<?= $TABLE_CELL ?>"><span class="mobile-label">Site Code:</span><span class="no-auto-link" style="color:#2d2d31; text-decoration:none; font-weight:500; display:inline;"><?= htmlspecialchars($site['site_code'] ?? '') ?></span></td>
              <td style="<?= $TABLE_CELL ?>"><span class="mobile-label">Site / Location:</span><span class="no-auto-link" style="color:#2d2d31; text-decoration:none; font-weight:500; display:inline;"><?= htmlspecialchars($site['site_name'] ?? '') ?></span></td>
              <td style="<?= $TABLE_CELL ?>"><span class="mobile-label">Base Rent:</span>₹ <?= htmlspecialchars($site['rent_amount'] ?? '') ?></td>
              <?php if ($show_appraisal_column): ?>
              <td style="<?= $TABLE_CELL ?>"><span class="mobile-label">Appraisal:</span><?= htmlspecialchars($site['appraisal_display'] ?? '–') ?></td>
              <?php endif; ?>
              <!-- <?php if ($show_gst_column): ?>
              <td style="<?= $TABLE_CELL ?>"><span class="mobile-label">GST Amount:</span><?= htmlspecialchars($site['gst_amount'] ?? '–') ?></td>
              <?php endif; ?> -->
              <td style="<?= $TABLE_CELL ?>"><span class="mobile-label">Net Payable:</span>₹ <?= htmlspecialchars($site['net_payable'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <table class="rent-table" role="presentation" style="width:100%; border-collapse:collapse;">
        <tr>
          <td class="rent-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">Total Rent Amount</td>
          <td class="rent-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;">₹ <?= htmlspecialchars($total_rent_amount) ?></td>
        </tr>
        <?php if ($show_appraisal_column): ?>
        <tr>
          <td class="rent-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">Total Appraisal Amount</td>
          <td class="rent-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;">
            <?php if ($is_appraisal_included_in_rent || $total_appraisal_amount === '0' || $total_appraisal_amount === '0.00' || $total_appraisal_amount === ''): ?>
              Already included in current rent<?php if (!empty($total_appraisal_amount_raw) && $total_appraisal_amount_raw !== '0' && $total_appraisal_amount_raw !== '0.00'): ?> (₹ <?= htmlspecialchars($total_appraisal_amount_raw) ?>)<?php endif; ?>
            <?php else: ?>
              ₹ <?= htmlspecialchars($total_appraisal_amount) ?>
            <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <tr>
          <td class="rent-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">
            Total GST Amount
            <?php if (!empty($rp_gst_pct_val)): ?>
              (<?= htmlspecialchars($rp_gst_pct_val) ?>%)
            <?php endif; ?>
          </td>
          <td class="rent-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;<?= $is_gst_hold ? ' color:#999999; text-decoration:line-through;' : ' color:#17171a;' ?>">
            ₹ <?= htmlspecialchars($total_gst_amount) ?>
            <?php if ($is_gst_hold): ?>
              <span class="hold-label" style="display:inline-block; margin-left:6px; padding:2px 6px; border-radius:4px; background:#fff0f2; color:#b32032; font-size:9px; font-weight:800; text-decoration:none;">ON HOLD</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php if ($is_tds_applicable || (!empty($total_tds_amount) && $total_tds_amount !== '0' && $total_tds_amount !== '0.00')): ?>
        <tr class="rent-negative">
          <td class="rent-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#b32032; font-weight:600; text-align:left; font-size:12px;<?= $is_tds_hold ? ' text-decoration:line-through;' : '' ?>">
            TDS (<?= htmlspecialchars($rp_tds_percentage ?: '10') ?>%)
          </td>
          <td class="rent-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#b32032; font-weight:800; text-align:right; font-size:12px; white-space:nowrap;<?= $is_tds_hold ? ' color:#999999; text-decoration:line-through;' : '' ?>">
            - ₹ <?= htmlspecialchars($total_tds_amount) ?>
            <?php if ($is_tds_hold): ?>
              <span class="hold-label" style="display:inline-block; margin-left:6px; padding:2px 6px; border-radius:4px; background:#fff0f2; color:#b32032; font-size:9px; font-weight:800; text-decoration:none;">ON HOLD</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endif; ?>
        <!-- <?php if ($gst_invoice_missing_any): ?>
        <tr>
          <td colspan="2" style="padding:8px 14px; border-bottom:1px solid #eee5e6; background:#fff8f8; text-align:left; color:#b32032; font-size:11px; font-weight:800;">
            GST invoice PDF is required but missing for one or more sites above.
          </td>
        </tr>
        <?php endif; ?> -->
        <tr class="rent-total">
          <td style="padding:13px 14px; border-bottom:0; background:#e2182d; color:#ffffff; text-align:left; font-size:14px; font-weight:800;">Grand Total</td>
          <td style="padding:13px 14px; border-bottom:0; background:#e2182d; color:#ffffff; text-align:right; font-size:14px; font-weight:800; white-space:nowrap;">₹ <?= htmlspecialchars($grand_total) ?></td>
        </tr>
      </table>
    </div>
    <?php else: ?>
    <!-- MEDIA INFORMATION FIRST -->
    <div class="section-card" style="width:100%; margin:0 0 18px 0; border:1px solid #eadfe0; border-radius:12px; overflow:hidden; background:#ffffff;">
      <div class="section-heading" style="padding:13px 16px; border-bottom:1px solid #eadfe0; background:#ffffff; color:#df1128; font-size:14px; line-height:1.2; font-weight:800; letter-spacing:0.35px; text-transform:uppercase;">
        <span class="heading-badge" style="display:inline-block; width:30px; height:30px; line-height:30px; margin-right:10px; border-radius:8px; background:#e2182d; color:#ffffff; text-align:center; font-size:11px; font-weight:800; vertical-align:middle;">MI</span>
        Media Information
      </div>

      <table class="media-grid" role="presentation" style="width:100%; border-collapse:collapse; table-layout:fixed;">
        <tr>
          <td style="width:50%; padding:15px 16px; border-right:1px solid #eadfe0; border-bottom:1px solid #eadfe0; vertical-align:middle;">
            <div class="info-label" style="margin:0 0 5px 0; color:#797980; font-size:10px; line-height:1.2; font-weight:700; letter-spacing:0.45px; text-transform:uppercase;">Media Name</div>
            <div class="info-value" style="margin:0; color:#17171a; font-size:14px; line-height:1.45; font-weight:700; word-break:break-word;"><?= htmlspecialchars($media_name) ?></div>
          </td>
          <td style="width:50%; padding:15px 16px; border-bottom:1px solid #eadfe0; vertical-align:middle;">
            <div class="info-label" style="margin:0 0 5px 0; color:#797980; font-size:10px; line-height:1.2; font-weight:700; letter-spacing:0.45px; text-transform:uppercase;">Media Code</div>
            <div class="info-value" style="margin:0; color:#17171a; font-size:14px; line-height:1.45; font-weight:700; word-break:break-word;"><?= htmlspecialchars($media_code) ?></div>
          </td>
        </tr>
        <tr>
          <td style="width:50%; padding:15px 16px; border-right:1px solid #eadfe0; vertical-align:middle;">
            <div class="info-label" style="margin:0 0 5px 0; color:#797980; font-size:10px; line-height:1.2; font-weight:700; letter-spacing:0.45px; text-transform:uppercase;">Media Type</div>
            <div class="info-value" style="margin:0; color:#17171a; font-size:14px; line-height:1.45; font-weight:700; word-break:break-word;"><?= htmlspecialchars($media_type) ?></div>
          </td>
          <td style="width:50%; padding:15px 16px; vertical-align:middle;">
            <div class="info-label" style="margin:0 0 5px 0; color:#797980; font-size:10px; line-height:1.2; font-weight:700; letter-spacing:0.45px; text-transform:uppercase;">Media Address</div>
            <div class="info-value" style="margin:0; color:#17171a; font-size:14px; line-height:1.45; font-weight:700; word-break:break-word;"><?= nl2br(htmlspecialchars($media_address_display)) ?></div>
          </td>
        </tr>
      </table>
    </div>

    <!-- RENT DETAILS -->
    <div class="section-card" style="width:100%; margin:0 0 18px 0; border:1px solid #eadfe0; border-radius:12px; overflow:hidden; background:#ffffff;">
      <div class="section-heading" style="padding:13px 16px; border-bottom:1px solid #eadfe0; background:#ffffff; color:#df1128; font-size:14px; line-height:1.2; font-weight:800; letter-spacing:0.35px; text-transform:uppercase;">
        <span class="heading-badge" style="display:inline-block; width:30px; height:30px; line-height:30px; margin-right:10px; border-radius:8px; background:#e2182d; color:#ffffff; text-align:center; font-size:11px; font-weight:800; vertical-align:middle;">RD</span>
        Rent Details
      </div>

      <table class="split-table" role="presentation" style="width:100%; border-collapse:collapse; table-layout:fixed;">
        <tr>
          <!-- LEFT: CALCULATION -->
          <td class="split-column" style="width:50%; padding:0; vertical-align:top; border:0; border-right:1px solid #eadfe0;">
            <table class="rent-table" role="presentation" style="width:100%; border-collapse:collapse;">
              <tr>
                <td class="rent-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">Base Rent</td>
                <td class="rent-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;">₹ <?= htmlspecialchars($rp_rental_base) ?></td>
              </tr>

              <?php if ($is_appraisal_applicable): ?>
              <tr>
                <td class="rent-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">
                  Appraisal
                </td>
                <td class="rent-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;"><?= htmlspecialchars($appraisal_display) ?></td>
              </tr>
              <?php endif; ?>

              <?php if ($is_gst_applicable): ?>
              <tr>
                <td class="rent-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; text-align:left; font-size:12px; font-weight:600;<?= $is_gst_hold ? ' color:#999999; text-decoration:line-through;' : ' color:#505057;' ?>">
                  GST (<?= htmlspecialchars($rp_gst_pct_val) ?>%)
                </td>
                <td class="rent-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;<?= $is_gst_hold ? ' color:#999999; text-decoration:line-through;' : ' color:#17171a;' ?>">
                  ₹ <?= htmlspecialchars($rp_gst_amt_calc) ?>
                  <?php if ($is_gst_hold): ?>
                    <span class="hold-label" style="display:inline-block; margin-left:6px; padding:2px 6px; border-radius:4px; background:#fff0f2; color:#b32032; font-size:9px; font-weight:800; text-decoration:none;">ON HOLD</span>
                  <?php endif; ?>
                </td>
              </tr>
              <tr>
                <td class="rent-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">GST Invoice</td>
                <td class="rent-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;<?= $gst_invoice_missing ? ' color:#b32032;' : ' color:#17171a;' ?>">
                  <?= $gst_invoice_attached ? 'Attached' : ($gst_invoice_missing ? 'Missing' : '–') ?>
                </td>
              </tr>
              <?php endif; ?>

              <?php if ($is_tds_applicable): ?>
              <tr class="rent-negative">
                <td class="rent-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; text-align:left; font-size:12px; font-weight:600;<?= $is_tds_hold ? ' color:#999999; text-decoration:line-through;' : ' color:#b32032;' ?>">
                  TDS (<?= htmlspecialchars($rp_tds_percentage) ?>%)
                </td>
                <td class="rent-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;<?= $is_tds_hold ? ' color:#999999; text-decoration:line-through;' : ' color:#b32032;' ?>">
                  - ₹ <?= htmlspecialchars($rp_tds_amt_calc) ?>
                  <?php if ($is_tds_hold): ?>
                    <span class="hold-label" style="display:inline-block; margin-left:6px; padding:2px 6px; border-radius:4px; background:#fff0f2; color:#b32032; font-size:9px; font-weight:800; text-decoration:none;">ON HOLD</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endif; ?>

              <tr class="rent-total">
                <td style="padding:13px 14px; border-bottom:0; background:#e2182d; color:#ffffff; text-align:left; font-size:14px; font-weight:800;">Grand Total Payable</td>
                <td style="padding:13px 14px; border-bottom:0; background:#e2182d; color:#ffffff; text-align:right; font-size:14px; font-weight:800; white-space:nowrap;">₹ <?= htmlspecialchars($rp_grand_total_payable) ?></td>
              </tr>
              <?php if ($is_tds_applicable): ?>
              <tr>
                <td colspan="2" style="padding:8px 14px; border-bottom:0; background:#fff8f8; text-align:left;">
                  <i class="tds-note-icon" style="display:inline-block; width:14px; height:14px; line-height:14px; margin-right:6px; border-radius:50%; background:#b32032; color:#ffffff; text-align:center; font-size:9px; font-weight:800; font-style:normal; vertical-align:middle;">i</i>
                  <span class="tds-note-text" style="font-size:10px; line-height:1.4; color:#55555b; vertical-align:middle;">TDS is shown separately and is not deducted from the Grand Total Payable.</span>
                </td>
              </tr>
              <?php endif; ?>
            </table>
          </td>

          <!-- RIGHT: PAYMENT SCHEDULE -->
          <td class="split-column" style="width:50%; padding:0; vertical-align:top; border:0;">
            <table class="schedule-table" role="presentation" style="width:100%; border-collapse:collapse;">
              <tr>
                <td class="schedule-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">Payment Frequency</td>
                <td class="schedule-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;"><?= htmlspecialchars($rp_payment_frequency) ?></td>
              </tr>
              <tr>
                <td class="schedule-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">Last Bill Paid Date</td>
                <td class="schedule-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;"><?= htmlspecialchars($rp_last_bill_date) ?></td>
              </tr>
              <?php if ($rp_current_bill_date !== ''): ?>
              <tr>
                <td class="schedule-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">Current Bill Date</td>
                <td class="schedule-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;"><?= htmlspecialchars($rp_current_bill_date) ?></td>
              </tr>
              <?php endif; ?>
              <tr>
                <td class="schedule-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">Next Bill Date</td>
                <td class="schedule-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;"><?= htmlspecialchars($rp_next_bill_date) ?></td>
              </tr>
              <tr>
                <td class="schedule-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">Total Rental Amount</td>
                <td class="schedule-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;">₹ <?= htmlspecialchars($rp_total_rental) ?></td>
              </tr>
              <?php if (!$is_multiple_landowners): ?>
              <tr>
                <td class="schedule-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">GST Applicable</td>
                <td class="schedule-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;"><?= htmlspecialchars($rp_gst_applicable) ?></td>
              </tr>
              <tr>
                <td class="schedule-label" style="width:60%; padding:11px 14px; border-bottom:1px solid #eee5e6; color:#505057; text-align:left; font-size:12px; font-weight:600;">TDS Applicable</td>
                <td class="schedule-value" style="padding:11px 14px; border-bottom:1px solid #eee5e6; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;"><?= htmlspecialchars($rp_tds_applicable) ?></td>
              </tr>
              <?php endif; ?>
              <tr>
                <td class="schedule-label" style="width:60%; padding:11px 14px; border-bottom:0; color:#505057; text-align:left; font-size:12px; font-weight:600;">Appraisal</td>
                <td class="schedule-value" style="padding:11px 14px; border-bottom:0; color:#17171a; text-align:right; font-size:12px; font-weight:700; white-space:nowrap;"><?= $is_appraisal_applicable ? 'Yes' : 'No' ?></td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </div>

    <!-- QUICK SUMMARY CARDS -->
    <?php
      // 3 base cards (frequency / last / next) + optional current bill date + optional appraisal
      $statCardCount   = 3 + ($rp_current_bill_date !== '' ? 1 : 0) + ($is_appraisal_applicable ? 1 : 0);
      $statColWidthPct = round(100 / $statCardCount, 2);
    ?>
    <table class="stat-table" role="presentation" style="width:100%; margin:0 0 18px 0; border-collapse:separate; table-layout:fixed;">
      <tr>
        <td class="stat-cell" style="width:<?= $statColWidthPct ?>%; padding:0 5px 0 0; border:0; vertical-align:top;">
          <div class="stat-card" style="height:104px; border:1px solid #eadfe0; border-radius:10px; background:#fffafa; text-align:center;">
            <table class="stat-inner" role="presentation" style="width:100%; height:104px; border-collapse:collapse;">
              <tr>
                <td style="padding:10px 9px; border:0; vertical-align:middle; text-align:center;">
                  <div class="stat-label" style="margin:0 0 6px 0; color:#77777e; font-size:9px; line-height:1.25; font-weight:800; letter-spacing:0.45px; text-transform:uppercase;">Payment Frequency</div>
                  <div class="stat-value" style="margin:0; color:#17171a; font-size:14px; line-height:1.25; font-weight:800;"><?= htmlspecialchars($rp_payment_frequency) ?></div>
                </td>
              </tr>
            </table>
          </div>
        </td>

        <td class="stat-cell" style="width:<?= $statColWidthPct ?>%; padding:0 5px; border:0; vertical-align:top;">
          <div class="stat-card" style="height:104px; border:1px solid #eadfe0; border-radius:10px; background:#fffafa; text-align:center;">
            <table class="stat-inner" role="presentation" style="width:100%; height:104px; border-collapse:collapse;">
              <tr>
                <td style="padding:10px 9px; border:0; vertical-align:middle; text-align:center;">
                  <div class="stat-label" style="margin:0 0 6px 0; color:#77777e; font-size:9px; line-height:1.25; font-weight:800; letter-spacing:0.45px; text-transform:uppercase;">Last Bill Date</div>
                  <div class="stat-value" style="margin:0; color:#17171a; font-size:14px; line-height:1.25; font-weight:800;"><?= htmlspecialchars($rp_last_bill_date) ?></div>
                </td>
              </tr>
            </table>
          </div>
        </td>

        <?php if ($rp_current_bill_date !== ''): ?>
        <td class="stat-cell" style="width:<?= $statColWidthPct ?>%; padding:0 5px; border:0; vertical-align:top;">
          <div class="stat-card" style="height:104px; border:1px solid #eadfe0; border-radius:10px; background:#fffafa; text-align:center;">
            <table class="stat-inner" role="presentation" style="width:100%; height:104px; border-collapse:collapse;">
              <tr>
                <td style="padding:10px 9px; border:0; vertical-align:middle; text-align:center;">
                  <div class="stat-label" style="margin:0 0 6px 0; color:#77777e; font-size:9px; line-height:1.25; font-weight:800; letter-spacing:0.45px; text-transform:uppercase;">Current Bill Date</div>
                  <div class="stat-value" style="margin:0; color:#17171a; font-size:14px; line-height:1.25; font-weight:800;"><?= htmlspecialchars($rp_current_bill_date) ?></div>
                </td>
              </tr>
            </table>
          </div>
        </td>
        <?php endif; ?>

        <td class="stat-cell" style="width:<?= $statColWidthPct ?>%; padding:0 <?= $is_appraisal_applicable ? '5px' : '0' ?> 0 5px; border:0; vertical-align:top;">
          <div class="stat-card" style="height:104px; border:1px solid #eadfe0; border-radius:10px; background:#fffafa; text-align:center;">
            <table class="stat-inner" role="presentation" style="width:100%; height:104px; border-collapse:collapse;">
              <tr>
                <td style="padding:10px 9px; border:0; vertical-align:middle; text-align:center;">
                  <div class="stat-label" style="margin:0 0 6px 0; color:#77777e; font-size:9px; line-height:1.25; font-weight:800; letter-spacing:0.45px; text-transform:uppercase;">Next Bill Date</div>
                  <div class="stat-value" style="margin:0; color:#17171a; font-size:14px; line-height:1.25; font-weight:800;"><?= htmlspecialchars($rp_next_bill_date) ?></div>
                </td>
              </tr>
            </table>
          </div>
        </td>

        <?php if ($is_appraisal_applicable): ?>
        <td class="stat-cell" style="width:<?= $statColWidthPct ?>%; padding:0 0 0 5px; border:0; vertical-align:top;">
          <div class="stat-card" style="height:104px; border:1px solid #eadfe0; border-radius:10px; background:#fffafa; text-align:center;">
            <table class="stat-inner" role="presentation" style="width:100%; height:104px; border-collapse:collapse;">
              <tr>
                <td style="padding:10px 9px; border:0; vertical-align:middle; text-align:center;">
                  <div class="stat-label" style="margin:0 0 6px 0; color:#77777e; font-size:9px; line-height:1.25; font-weight:800; letter-spacing:0.45px; text-transform:uppercase;">Appraisal</div>
                  <div class="stat-value" style="margin:0; color:#17171a; font-size:14px; line-height:1.25; font-weight:800;"><?= htmlspecialchars($appraisal_display) ?></div>
                  <div class="stat-sub" style="margin:5px 0 0 0; color:#77777e; font-size:9px; line-height:1.4; font-weight:500;">
                    Last: <?= htmlspecialchars($rp_last_appraisal_date) ?><br>
                    Next: <?= htmlspecialchars($rp_next_appraisal_date) ?>
                  </div>
                </td>
              </tr>
            </table>
          </div>
        </td>
        <?php endif; ?>
      </tr>
    </table>

    <!-- NOTES -->
    <?php if ($has_notes): ?>
    <div class="notes-box" style="margin:0 0 18px 0; padding:13px 15px; border:1px solid #f0d6da; border-left:4px solid #e2182d; border-radius:8px; background:#fff8f8;">
      <div class="notes-title" style="margin:0 0 6px 0; color:#202024; font-size:12px; line-height:1.3; font-weight:800;">Notes</div>

      <?php if ($is_appraisal_applicable): ?>
      <p class="notes-copy" style="margin:0 0 4px 0; color:#55555b; font-size:11px; line-height:1.6;">
        Appraisal included in Base Rent.
      </p>
      <?php endif; ?>

      <?php if ($is_gst_applicable): ?>
      <p class="notes-copy" style="margin:0 0 4px 0; color:#55555b; font-size:11px; line-height:1.6;">
        GST <?= htmlspecialchars($rp_gst_pct_val) ?>% on ₹ <?= htmlspecialchars($rp_total_with_appraisal) ?> = ₹ <?= htmlspecialchars($rp_gst_amt_calc) ?>.
      </p>
      <?php endif; ?>

      <?php if ($gst_invoice_missing): ?>
      <p class="notes-copy notes-hold" style="margin:0 0 4px 0; color:#b32032; font-size:11px; line-height:1.6; font-weight:800;">GST invoice PDF is required but was not provided for this bill.</p>
      <?php endif; ?>

      <?php if ($is_tds_applicable): ?>
      <p class="notes-copy" style="margin:0 0 4px 0; color:#55555b; font-size:11px; line-height:1.6;">
        TDS <?= htmlspecialchars($rp_tds_percentage) ?>% on ₹ <?= htmlspecialchars($rp_tds_base_amount) ?> (online payment) = ₹ <?= htmlspecialchars($rp_tds_amt_calc) ?>.
      </p>
      <?php endif; ?>

      <?php if ($is_gst_hold): ?>
      <p class="notes-copy notes-hold" style="margin:0 0 4px 0; color:#b32032; font-size:11px; line-height:1.6; font-weight:800;">GST is on hold.</p>
      <?php endif; ?>

      <?php if (!$is_multiple_landowners && $is_tds_applicable && $is_tds_hold): ?>
      <p class="notes-copy notes-hold" style="margin:0 0 4px 0; color:#b32032; font-size:11px; line-height:1.6; font-weight:800;">TDS is on hold.</p>
      <?php endif; ?>

      <?php if ($is_multiple_landowners): ?>
        <?php foreach ($multi_tds_hold_notes as $tdsHoldNote): ?>
        <p class="notes-copy notes-hold" style="margin:0 0 4px 0; color:#b32032; font-size:11px; line-height:1.6; font-weight:800;"><?= htmlspecialchars($tdsHoldNote) ?></p>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>

    <!-- TDS DEDUCTION SUMMARY FOR MULTIPLE LANDLORDS (shared by both the single-site and
         multi-site consolidated flows) -->
    <?php if ($is_multiple_landowners && !empty($multi_tds_deducted_rows)): ?>
    <div class="section-card" style="width:100%; margin:0 0 18px 0; border:1px solid #eadfe0; border-radius:12px; overflow:hidden; background:#ffffff;">
      <div class="section-heading-red" style="padding:12px 16px; background:#e2182d; color:#ffffff; font-size:13px; line-height:1.2; font-weight:800; letter-spacing:0.35px; text-transform:uppercase;">
        <span class="heading-badge-light" style="display:inline-block; min-width:30px; height:26px; line-height:26px; margin-right:9px; padding:0 7px; border-radius:7px; background:rgba(255,255,255,0.18); color:#ffffff; text-align:center; font-size:10px; font-weight:800; vertical-align:middle;">TDS</span>
        TDS Deduction Summary
      </div>

      <div class="table-wrap" style="width:100%; overflow-x:auto;">
        <table class="responsive-table" style="width:100%; border-collapse:collapse;">
          <thead>
            <tr>
              <th style="<?= $TABLE_HEAD ?>">Owner Name</th>
              <th style="<?= $TABLE_HEAD ?>">TDS %</th>
              <th style="<?= $TABLE_HEAD ?>">TDS Amount</th>
              <th style="<?= $TABLE_HEAD ?>">TDS Hold</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($multi_tds_deducted_rows as $row): ?>
            <tr>
              <td style="<?= $TABLE_CELL ?>"><span class="mobile-label">Owner Name:</span><?= htmlspecialchars($row['owner_name'] ?? '') ?></td>
              <td style="<?= $TABLE_CELL ?>"><span class="mobile-label">TDS %:</span><?= htmlspecialchars($row['tds_percentage'] ?? '') ?>%</td>
              <td style="<?= $TABLE_CELL ?><?= !empty($row['is_tds_hold']) ? ' color:#999999; text-decoration:line-through;' : ' color:#b32032; font-weight:800;' ?>"><span class="mobile-label">TDS Amount:</span>- ₹ <?= htmlspecialchars($row['tds_amount'] ?? '') ?></td>
              <td style="<?= $TABLE_CELL ?>"><span class="mobile-label">TDS Hold:</span><?= htmlspecialchars($row['tds_hold'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- SINGLE / MULTIPLE LANDLORD DETAILS -->
    <?php if (!empty($land_owners)): ?>
    <div class="section-card" style="width:100%; margin:0 0 18px 0; border:1px solid #eadfe0; border-radius:12px; overflow:hidden; background:#ffffff;">
      <div class="section-heading-red" style="padding:12px 16px; background:#e2182d; color:#ffffff; font-size:13px; line-height:1.2; font-weight:800; letter-spacing:0.35px; text-transform:uppercase;">
        <span class="heading-badge-light" style="display:inline-block; min-width:30px; height:26px; line-height:26px; margin-right:9px; padding:0 7px; border-radius:7px; background:rgba(255,255,255,0.18); color:#ffffff; text-align:center; font-size:10px; font-weight:800; vertical-align:middle;">LO</span>
        <?= $is_multiple_landowners ? 'Multiple Landlord Details' : 'Landlord Details' ?>
        <?php if ($landlord_count > 0): ?>
          <span style="font-weight:600; opacity:0.9;">(<?= htmlspecialchars((string)$landlord_count) ?>)</span>
        <?php endif; ?>
      </div>

      <div style="padding:14px;">
        <?php foreach ($land_owners as $idx => $owner): ?>
        <div class="owner-card" style="margin:0 0 14px 0; border:1px solid #eadfe0; border-radius:10px; overflow:hidden; background:#ffffff;">
          <div class="owner-heading" style="padding:11px 14px; background:#2b2b2f; color:#ffffff; font-size:12px; line-height:1.35; font-weight:800;">
            LAND OWNER <?= htmlspecialchars(str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT)) ?>
            <?php if (!empty($owner['owner_name'])): ?>
              <span class="owner-heading-name" style="color:#ffffff; font-weight:600;"> - <?= htmlspecialchars($owner['owner_name']) ?></span>
            <?php endif; ?>
          </div>

          <table class="owner-summary-table" role="presentation" style="width:100%; border-collapse:collapse; table-layout:fixed;">
            <tr>
              <!-- OWNER AND BANK DETAILS -->
              <td class="owner-summary-column" style="<?= $is_multiple_landowners ? 'width:50%; padding:0; border:0; border-right:1px solid #eadfe0; vertical-align:top;' : 'width:100%; padding:0; border:0; vertical-align:top;' ?>">
                <div class="subsection-title" style="padding:9px 12px; background:#fff5f6; border-bottom:1px solid #eadfe0; color:#b8192d; font-size:10px; line-height:1.3; font-weight:800; letter-spacing:0.5px; text-transform:uppercase;">Owner and Bank Details</div>
                <table class="detail-table" role="presentation" style="width:100%; border-collapse:collapse;">
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">S.No</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($owner['sno'] ?? ($idx + 1)) ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Owner Name</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars(($owner['owner_name'] ?? '') !== '' ? $owner['owner_name'] : '–') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Phone</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars(($owner['phone'] ?? '') !== '' ? $owner['phone'] : '–') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Bank Name</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars(($owner['bank_name'] ?? '') !== '' ? $owner['bank_name'] : '–') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">IFSC Code</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars(($owner['ifsc'] ?? '') !== '' ? $owner['ifsc'] : '–') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Account No</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left; word-break:break-word;"><?= htmlspecialchars(($owner['account_no'] ?? '') !== '' ? $owner['account_no'] : '–') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">PAN Number</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars(($owner['pan'] ?? '') !== '' ? $owner['pan'] : '–') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Payment Category</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($owner['payment_category'] ?? '') ?></td>
                  </tr>
                  <?php // Single landlord has no Payment Breakdown column, so show the Cash + Online split here ?>
                  <?php if (!$is_multiple_landowners && ($owner['payment_category'] ?? '') === 'Cash + Online'): ?>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Online Amount</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;">₹ <?= htmlspecialchars($owner['online_amount'] ?? '0') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Cash Amount</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;">₹ <?= htmlspecialchars($owner['cash_amount'] ?? '0') ?></td>
                  </tr>
                  <?php endif; ?>
                  <?php
                  $ownerGstAmtRaw = (float)str_replace([',', '₹', ' '], '', $owner['gst_amount'] ?? '0');
                  $ownerGstNumVal = trim((string)($owner['gst_number'] ?? ''));
                  $hasOwnerGstDetails = ($ownerGstAmtRaw > 0) || ($ownerGstNumVal !== '');
                  ?>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; <?= $hasOwnerGstDetails ? 'border-bottom:1px solid #eee5e6;' : 'border-bottom:0;' ?> background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Online Mode</td>
                    <td class="detail-value" style="padding:9px 11px; <?= $hasOwnerGstDetails ? 'border-bottom:1px solid #eee5e6;' : 'border-bottom:0;' ?> color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($owner['online_mode'] ?? '') ?></td>
                  </tr>
                  <?php if ($hasOwnerGstDetails): ?>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">GST Amount</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;">₹ <?= htmlspecialchars($owner['gst_amount'] ?? '0') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:0; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">GST Number</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:0; color:#26262a; font-size:11px; font-weight:600; text-align:left; word-break:break-word;"><?= htmlspecialchars($owner['gst_number'] ?? '') ?></td>
                  </tr>
                  <?php endif; ?>
                </table>
              </td>

              <!-- PAYMENT BREAKDOWN (multi-landlord only) -->
              <?php if ($is_multiple_landowners): ?>
              <td class="owner-summary-column" style="width:50%; padding:0; border:0; vertical-align:top;">
                <div class="subsection-title" style="padding:9px 12px; background:#fff5f6; border-bottom:1px solid #eadfe0; color:#b8192d; font-size:10px; line-height:1.3; font-weight:800; letter-spacing:0.5px; text-transform:uppercase;">Payment Breakdown</div>
                <table class="detail-table" role="presentation" style="width:100%; border-collapse:collapse;">
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Share Amount</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:right;">₹ <?= htmlspecialchars($owner['share_amount'] ?? '') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Online Amount</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:right;">₹ <?= htmlspecialchars($owner['online_amount'] ?? '') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Cash Amount</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:right;">₹ <?= htmlspecialchars($owner['cash_amount'] ?? '') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">TDS Applicable</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:right;"><?= htmlspecialchars($owner['tds_applicable'] ?? '') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;<?= !empty($owner['is_tds_hold']) ? ' color:#999999; text-decoration:line-through;' : '' ?>">TDS Amount</td>
                    <td class="detail-value payment-highlight" style="padding:9px 11px; border-bottom:1px solid #eee5e6; text-align:right; font-size:11px; font-weight:800;<?= !empty($owner['is_tds_hold']) ? ' color:#999999; text-decoration:line-through;' : ' color:#b8192d;' ?>">- ₹ <?= htmlspecialchars($owner['tds_amount'] ?? '') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">TDS Hold</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:right;"><?= htmlspecialchars($owner['tds_hold'] ?? '') ?></td>
                  </tr>
                  <tr>
                    <td class="detail-label" style="width:48%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Net Online Paid</td>
                    <td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:700; text-align:right;">₹ <?= htmlspecialchars($owner['net_online_paid'] ?? '') ?></td>
                  </tr>
                  <tr class="payment-total">
                    <td style="padding:10px 11px; border-bottom:0; background:#fff3f4; color:#b8192d; font-size:11px; font-weight:800; text-align:left;">Total Amount</td>
                    <td style="padding:10px 11px; border-bottom:0; background:#fff3f4; color:#b8192d; font-size:11px; font-weight:800; text-align:right;">₹ <?= htmlspecialchars($owner['total_with_gst'] ?? '') ?></td>
                  </tr>
                </table>
              </td>
              <?php endif; ?>
            </tr>
          </table>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- DETAILED MEDIA INFORMATION (PER SITE OR SINGLE) -->
    <?php if (!$is_multi_site): ?>
    <?php
    $mediaListToRender = !empty($media_details_list) ? $media_details_list : [[
        'media_code' => $media_code,
        'media_name' => $media_name,
        'media_type' => $media_type,
        'state' => $state,
        'city' => $city,
        'location' => $location,
        'full_address' => $full_address,
        'width_ft' => $width_ft,
        'height_ft' => $height_ft,
        'total_sqft' => $total_sqft,
        'num_land_owners' => $num_land_owners,
        'media_status' => $media_status,
    ]];
    foreach ($mediaListToRender as $mItem):
    ?>
    <div class="section-card" style="width:100%; margin:0 0 18px 0; border:1px solid #eadfe0; border-radius:12px; overflow:hidden; background:#ffffff;">
      <div class="section-heading-red" style="padding:12px 16px; background:#e2182d; color:#ffffff; font-size:13px; line-height:1.2; font-weight:800; letter-spacing:0.35px; text-transform:uppercase;">
        <span class="heading-badge-light" style="display:inline-block; min-width:30px; height:26px; line-height:26px; margin-right:9px; padding:0 7px; border-radius:7px; background:rgba(255,255,255,0.18); color:#ffffff; text-align:center; font-size:10px; font-weight:800; vertical-align:middle;">MD</span>
        Media Details <?= !empty($media_details_list) ? ' - ' . htmlspecialchars($mItem['media_code'] ?? '') : '' ?>
      </div>
      <table class="detail-table" role="presentation" style="width:100%; border-collapse:collapse;">
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Media Code</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($mItem['media_code'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Media Name</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($mItem['media_name'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Media Type</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($mItem['media_type'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">State</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($mItem['state'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">City</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($mItem['city'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Location</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left; word-break:break-word;"><?= htmlspecialchars($mItem['location'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Full Address</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left; word-break:break-word;"><?= nl2br(htmlspecialchars($mItem['full_address'] ?? '')) ?></td></tr>
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Width (ft)</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($mItem['width_ft'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Height (ft)</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($mItem['height_ft'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Total Sq Ft</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($mItem['total_sqft'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">No. of Land Owners</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($mItem['num_land_owners'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:45%; padding:9px 11px; border-bottom:0; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Status</td><td class="detail-value" style="padding:9px 11px; border-bottom:0; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($mItem['media_status'] ?? '') ?></td></tr>
      </table>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <!-- AGREEMENT DETAILS CARD(S) -->
    <?php if ($is_separate_agreement && !empty($agreement_details_list)): ?>
    <?php foreach ($agreement_details_list as $aIdx => $aItem): ?>
    <div class="section-card" style="width:100%; margin:0 0 18px 0; border:1px solid #eadfe0; border-radius:12px; overflow:hidden; background:#ffffff;">
      <div class="section-heading-red" style="padding:12px 16px; background:#e2182d; color:#ffffff; font-size:13px; line-height:1.2; font-weight:800; letter-spacing:0.35px; text-transform:uppercase;">
        <span class="heading-badge-light" style="display:inline-block; min-width:30px; height:26px; line-height:26px; margin-right:9px; padding:0 7px; border-radius:7px; background:rgba(255,255,255,0.18); color:#ffffff; text-align:center; font-size:10px; font-weight:800; vertical-align:middle;">AD</span>
        Agreement Details <?= count($agreement_details_list) > 1 ? ' - ' . htmlspecialchars($aItem['media_code'] ?? '') . (!empty($aItem['media_name']) ? ' (' . htmlspecialchars($aItem['media_name']) . ')' : '') : '' ?>
      </div>
      <table class="detail-table" role="presentation" style="width:100%; border-collapse:collapse;">
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Agreement Start Date</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($aItem['agree_start_date'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Agreement End Date</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($aItem['agree_end_date'] ?? '') ?></td></tr>
        <!-- <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Reminder Before Expiry (Days)</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($aItem['reminder_days'] ?? '') ?></td></tr> -->
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Advance Rent</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;">₹ <?= htmlspecialchars($aItem['advance_rent'] ?? '0') ?></td></tr>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Total Rental Amount</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;">₹ <?= htmlspecialchars($aItem['total_rental_amt'] ?? '0') ?></td></tr>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Payment Frequency</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($aItem['payment_frequency'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Last Bill Paid Date</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($aItem['last_bill_date'] ?? '') ?></td></tr>
        <?php if (!empty($aItem['current_bill_date'])): ?>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Current Bill Date</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($aItem['current_bill_date']) ?></td></tr>
        <?php endif; ?>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Next Billing Date</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($aItem['next_billing_date'] ?? '') ?></td></tr>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:0; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Status</td><td class="detail-value" style="padding:9px 11px; border-bottom:0; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($aItem['agree_status'] ?? '') ?></td></tr>
      </table>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <!-- SINGLE AGREEMENT DETAILS CARD -->
    <div class="section-card" style="width:100%; margin:0 0 18px 0; border:1px solid #eadfe0; border-radius:12px; overflow:hidden; background:#ffffff;">
      <div class="section-heading-red" style="padding:12px 16px; background:#e2182d; color:#ffffff; font-size:13px; line-height:1.2; font-weight:800; letter-spacing:0.35px; text-transform:uppercase;">
        <span class="heading-badge-light" style="display:inline-block; min-width:30px; height:26px; line-height:26px; margin-right:9px; padding:0 7px; border-radius:7px; background:rgba(255,255,255,0.18); color:#ffffff; text-align:center; font-size:10px; font-weight:800; vertical-align:middle;">AD</span>
        Agreement Details
      </div>
      <table class="detail-table" role="presentation" style="width:100%; border-collapse:collapse;">
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Agreement Start Date</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($agree_start_date) ?></td></tr>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Agreement End Date</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($agree_end_date) ?></td></tr>
        <!-- <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Reminder Before Expiry (Days)</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($reminder_days) ?></td></tr> -->
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Advance Rent</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;">₹ <?= htmlspecialchars($advance_rent) ?></td></tr>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Total Rental Amount</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;">₹ <?= htmlspecialchars($total_rental_amt) ?></td></tr>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Payment Frequency</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($payment_frequency) ?></td></tr>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Last Bill Paid Date</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($last_bill_date) ?></td></tr>
        <?php if ($current_bill_date !== ''): ?>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Current Bill Date</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($current_bill_date) ?></td></tr>
        <?php endif; ?>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:1px solid #eee5e6; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Next Billing Date</td><td class="detail-value" style="padding:9px 11px; border-bottom:1px solid #eee5e6; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($next_billing_date) ?></td></tr>
        <tr><td class="detail-label" style="width:55%; padding:9px 11px; border-bottom:0; background:#fffafa; color:#5b5b61; font-size:11px; font-weight:700; text-align:left;">Status</td><td class="detail-value" style="padding:9px 11px; border-bottom:0; color:#26262a; font-size:11px; font-weight:600; text-align:left;"><?= htmlspecialchars($agree_status) ?></td></tr>
      </table>
    </div>
    <?php endif; ?>
    <p class="closing-copy" style="margin:4px 0 18px 0; color:#55555b; font-size:12px; line-height:1.6;">
      Please review the details and provide your approval at the earliest convenience.
    </p>

  </div>

  <!-- FOOTER -->
  <table class="footer-table" role="presentation" style="width:100%; background:#f3f4f5; border-collapse:collapse;">
    <tr>
      <td style="padding:18px 28px; border:0; background:#f3f4f5; color:#56565c; font-size:10px; line-height:1.55; vertical-align:middle;">
        This is an automated email from ADINN Rental Management System.<br>
        Please review the details and provide your approval.
      </td>
      <td class="footer-brand" style="padding:18px 28px; border:0; background:#f3f4f5; color:#2f2f34; font-size:10px; line-height:1.55; vertical-align:middle; text-align:right; white-space:nowrap; font-weight:700;">
        © <?= date('Y') ?> Adinn Advertising Services Ltd. All Rights Reserved.
      </td>
    </tr>
  </table>

</div>
</body>
</html>
