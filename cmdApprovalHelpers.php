<?php
// ─────────────────────────────────────────
// Pure business-logic helpers for CMD Approval mail generation.
// No PHPMailer / HTTP / header() calls in this file on purpose — it is
// required by cmdApprovalSK.php for the live mail flow, and can also be
// required standalone (e.g. by tests/run_tests.php) since it has zero
// external dependencies and never touches vendor/autoload.php.
// ─────────────────────────────────────────

// ─────────────────────────────────────────
// ENUM MAPS
// ⚠️ Only values confirmed against sample payloads are marked "confirmed".
//    Everything else is a best-guess placeholder — please confirm with the
//    backend dev and edit these arrays if wrong. Template design is NOT
//    touched by any of this; only these maps decide what text is shown.
// ─────────────────────────────────────────
$MEDIA_STATUS = [
    1 => 'Active',    // confirmed (from sample)
    2 => 'Inactive',  // guess
];
$AGREEMENT_STATUS = [
    1 => 'Draft',
    2 => 'Pending Approval',
    3 => 'Active',
    4 => 'Expired',
    5 => 'Expiring Soon',
];
$PAYMENT_FREQUENCY = [
    1 => 'Monthly',
    2 => '3 Months',
    3 => '6 Months',
    4 => 'Yearly Once',
    5 => '2 Years Once',
    6 => 'Custom',
];

$APPRAISAL_FREQUENCY = [
    1 => '1 Year',
    2 => '2 Years',
    3 => '3 Years',
    4 => 'Custom',
];

$APPRAISAL_TYPE = [
    1 => 'Percentage',
    2 => 'Fixed Amount',
];

$PAYMENT_CATEGORY = [
    1 => 'Cash',
    2 => 'Online',
    3 => 'Cash + Online',
];
$ONLINE_MODE = [
    1 => 'Bank Transfer',
    2 => 'UPI',
    3 => 'Cheque',
];
// $SHARE_TYPE = [
//     1 => 'Percentage Share',
//     2 => 'Equal Share',
//     3 => 'Fixed Amount',
// ];

$GST_TDS_APPLICABLE = [
    0 => 'No',
    1 => 'Yes',
 ];

function mapVal($map, $code, $fallbackPrefix = 'Code ') {
    if ($code === null || $code === '') return '';
    return $map[$code] ?? ($fallbackPrefix . $code);
}
function fmtDate($iso, $format = 'd-M-Y') {
    if (empty($iso)) return '';
    try {
        return (new DateTime($iso))->format($format);
    } catch (Exception $e) {
        return $iso;
    }
}
function fmtAmount($n) {
    if ($n === null || $n === '') return '';
    $n = (float)$n;
    $isNegative = $n < 0;
    $numStr = number_format(abs($n), 0, '.', '');
    $len = strlen($numStr);
    if ($len <= 3) {
        $formatted = $numStr;
    } else {
        $lastThree = substr($numStr, -3);
        $remaining = substr($numStr, 0, $len - 3);
        $remaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);
        $formatted = $remaining . ',' . $lastThree;
    }
    return ($isNegative ? '-' : '') . $formatted;
}
function fetchRemoteFile($url, $timeoutSec = 15) {
    $context = stream_context_create([
        'http'  => ['timeout' => $timeoutSec, 'header' => "User-Agent: cmd-mail\r\n"],
        'https' => ['timeout' => $timeoutSec, 'header' => "User-Agent: cmd-mail\r\n"],
    ]);
    $data = @file_get_contents($url, false, $context);
    return $data === false ? null : $data;
}
function fmtHistoryLatest($history, $dateFormat = 'd-M-Y') {
    if (empty($history) || !is_array($history)) return '';
    $latest = end($history); // last entry in the array
    $amt    = isset($latest['amount']) ? '₹ ' . number_format((float)$latest['amount']) : '';
    $by     = $latest['updatedBy'] ?? '';
    $on     = '';
    if (!empty($latest['updatedAt'])) {
        try {
            $on = (new DateTime($latest['updatedAt']))->format($dateFormat);
        } catch (Exception $e) {
            $on = $latest['updatedAt'];
        }
    }
    $parts = array_filter([$amt, $by ? "by $by" : '', $on ? "on $on" : '']);
    return implode(' ', $parts);
}

// ─────────────────────────────────────────
// Per-owner processing: GST/TDS-hold flags + the "Landlord Details" table rows
// + multi-landlord TDS notes/summary. Identical logic for 1 owner or many —
// extracted so both the single-site flow and the multi-site consolidated flow
// (billingType) can reuse the exact same GST/TDS business rules.
// ─────────────────────────────────────────
function processLandOwners(array $landOwnersRaw) {
    global $GST_TDS_APPLICABLE, $PAYMENT_CATEGORY, $ONLINE_MODE;

    // ── LANDLORD MODE: single vs multiple (drives notes vs table in template) ──
    $isMultipleLandowners = count($landOwnersRaw) > 1;

    // Single-landlord GST/TDS hold flags (read straight off owner records)
    $singleOwner = $landOwnersRaw[0] ?? [];
    $isGstHold   = false;
    foreach ($landOwnersRaw as $o) {
        if (!empty($o['gstHold']) && (int)$o['gstHold'] === 1) {
            $isGstHold = true;
            break;
        }
    }
    $isTdsHold   = !empty($singleOwner['tdsHold']) && (int)$singleOwner['tdsHold'] === 1;

    $landOwners = [];
    $multiTdsHoldNotes = [];
    $multiTdsDeductedRows = [];
    foreach ($landOwnersRaw as $i => $o) {
        $ownerOnlineAmount  = (float)($o['onlineAmount'] ?? 0);
        $ownerTdsAppFlag    = isset($o['tdsApplicable']) ? (int)$o['tdsApplicable'] : ($ownerOnlineAmount >= 50000 ? 1 : 0);
        $ownerTdsEligible   = $ownerTdsAppFlag === 1;
        $ownerTdsAmt        = $ownerTdsEligible ? round($ownerOnlineAmount * (($o['tdsPercentage'] ?? 0) / 100), 2) : 0;
        $ownerTdsApplicable = $ownerTdsEligible && $ownerTdsAmt > 0;
        $ownerTdsHoldFlag   = !empty($o['tdsHold']) && (int)$o['tdsHold'] === 1;

        $ownerGstApp = isset($o['gstApplicable']) ? (int)$o['gstApplicable'] : 0;
        $ownerGstAmt = isset($o['gstAmount']) ? (float)$o['gstAmount'] : 0;
        $ownerGstNum = $o['gstNumber'] ?? '';

        $ownerTotalWithGst = (float)($o['totalAmountWithGst'] ?? 0);
        if ($ownerTotalWithGst <= 0) {
            $share = (float)($o['shareAmount'] ?? ((float)($o['onlineAmount'] ?? 0) + (float)($o['cashAmount'] ?? 0)));
            $ownerTotalWithGst = $share + $ownerGstAmt;
        }

        $landOwners[] = [
            'sno'            => $i + 1,
            'owner_name'     => !empty(trim((string)($o['name'] ?? ''))) ? $o['name'] : '–',
            'phone'          => !empty(trim((string)($o['phone'] ?? ''))) ? $o['phone'] : '–',
            'bank_name'      => !empty(trim((string)($o['bankName'] ?? ''))) ? $o['bankName'] : '–',
            'ifsc'           => !empty(trim((string)($o['ifsc'] ?? ''))) ? $o['ifsc'] : '–',
            'account_no'     => !empty(trim((string)($o['accountNumber'] ?? ''))) ? $o['accountNumber'] : '–',
            'pan'            => !empty(trim((string)($o['panNumber'] ?? ''))) ? $o['panNumber'] : '–',
            'payment_category' => mapVal($PAYMENT_CATEGORY, $o['paymentCategory'] ?? null),
            'online_mode'    => !empty($o['onlineMode']) ? mapVal($ONLINE_MODE, $o['onlineMode']) : '–',
            'share_amount'   => fmtAmount($o['shareAmount']  ?? 0),
            'online_amount'  => fmtAmount($o['onlineAmount'] ?? 0),
            'cash_amount'    => fmtAmount($o['cashAmount']   ?? 0),
            'gst_applicable' => mapVal($GST_TDS_APPLICABLE, $ownerGstApp),
            'gst_amount'     => fmtAmount($ownerGstAmt),
            'gst_number'     => $ownerGstNum,
            'tds_applicable' => mapVal($GST_TDS_APPLICABLE, $ownerTdsApplicable ? 1 : 0),
            'tds_amount'     => fmtAmount($ownerTdsAmt),
            'tds_hold'       => mapVal($GST_TDS_APPLICABLE, $ownerTdsHoldFlag ? 1 : 0),
            'is_tds_hold'    => $ownerTdsHoldFlag,
            'net_online_paid' => fmtAmount($ownerOnlineAmount - $ownerTdsAmt),
            'total_with_gst' => fmtAmount($ownerTotalWithGst),
        ];

        if ($isMultipleLandowners) {
            if ($ownerTdsHoldFlag && $ownerTdsApplicable) {
                $multiTdsHoldNotes[] = 'TDS is on hold for ' . ($o['name'] ?? 'this owner') . '.';
            }

            if ($ownerTdsApplicable) {
                $multiTdsDeductedRows[] = [
                    'owner_name'     => $o['name'] ?? '',
                    'tds_percentage' => $o['tdsPercentage'] ?? 0,
                    'tds_amount'     => fmtAmount($ownerTdsAmt),
                    'tds_hold'       => mapVal($GST_TDS_APPLICABLE, $ownerTdsHoldFlag ? 1 : 0),
                    'is_tds_hold'    => $ownerTdsHoldFlag,
                ];
            }
        }
    }

    return [
        'land_owners'             => $landOwners,
        'is_multiple_landowners'  => $isMultipleLandowners,
        'multi_tds_hold_notes'    => $multiTdsHoldNotes,
        'multi_tds_deducted_rows' => $multiTdsDeductedRows,
        'single_owner'            => $singleOwner,
        'is_gst_hold'             => $isGstHold,
        'is_tds_hold'             => $isTdsHold,
    ];
}

// ─────────────────────────────────────────
// GST invoice PDF is mandatory whenever GST is applicable (business rule).
// Rather than hard-rejecting the request (which would risk breaking existing
// live callers that predate this field), missing invoices are surfaced as a
// clearly flagged "GST Invoice: Missing" note in the email itself so CMD /
// Accounts can see it before approving. See cmdApprovalSK.php for how the
// returned invoice_urls get attached once present.
// ─────────────────────────────────────────
function resolveGstInvoice($rentalPayment, $isGstApplicable) {
    $url = trim((string)($rentalPayment['gstInvoiceUrl'] ?? ''));
    return [
        'url'      => $url,
        'attached' => $url !== '',
        'missing'  => $isGstApplicable && $url === '',
    ];
}

// ─────────────────────────────────────────
// Build the flat template payload for ONE site/media object — this is the
// original (legacy) single-site mapping, unchanged in behavior. Used for:
//  - legacy calls (no billingType / no sites[])
//  - separate_bill + single landlord + multiple sites (called once per site)
// ─────────────────────────────────────────
function buildSingleMediaPayload(array $media) {
    global $MEDIA_STATUS, $AGREEMENT_STATUS, $PAYMENT_FREQUENCY, $APPRAISAL_FREQUENCY, $APPRAISAL_TYPE, $GST_TDS_APPLICABLE;

    // ─────────────────────────────────────────
    // Map nested backend payload → flat template fields
    // (template variable names below are UNCHANGED from the design file)
    // ─────────────────────────────────────────
    $rentalPayment = $media['rentalPayment'] ?? [];
    $agreement     = $media['agreement']     ?? [];
    $appraisal     = $media['appraisal']     ?? [];
    $landOwnersRaw = $media['landOwners']    ?? [];

    // Proof-of-campaign images — array of URLs, sent as mail attachments only (not shown in body).
    $proofOfCampaignUrls = $media['proof_of_campaign'] ?? [];
    if (!is_array($proofOfCampaignUrls)) {
        $proofOfCampaignUrls = !empty($proofOfCampaignUrls) ? [$proofOfCampaignUrls] : [];
    }

    $owners = processLandOwners($landOwnersRaw);
    $landOwners            = $owners['land_owners'];
    $isMultipleLandowners  = $owners['is_multiple_landowners'];
    $multiTdsHoldNotes     = $owners['multi_tds_hold_notes'];
    $multiTdsDeductedRows  = $owners['multi_tds_deducted_rows'];
    $singleOwner           = $owners['single_owner'];
    $isGstHold              = $owners['is_gst_hold'];
    $isTdsHold              = $owners['is_tds_hold'];

    $gstApplicableFlag = (isset($singleOwner['gstApplicable']) && (int)$singleOwner['gstApplicable'] === 1)
        || (isset($rentalPayment['gstApplicable']) && (int)$rentalPayment['gstApplicable'] === 1)
        ? 1 : 0;

    $gst_amount_val    = (isset($singleOwner['gstAmount']) && (float)$singleOwner['gstAmount'] > 0)
        ? (float)$singleOwner['gstAmount']
        : (float)($rentalPayment['gstAmount'] ?? 0);

    $gstNumberVal      = $singleOwner['gstNumber'] ?? $rentalPayment['gstNumber'] ?? '';
    $grand_total = fmtAmount($appraisal['currentRent'] ?? $rentalPayment['totalRentalAmountWithGst'] ?? 0);

    // ── CONDITIONAL FLAGS (drive both intro text and table row visibility) ──
    // GST/TDS at the rentalPayment level only make sense as one combined figure for a single
    // landlord. For multiple landlords, GST/TDS vary per owner and are shown per-row in the
    // owner table instead — the summary rows for both are hidden in that case. This is also
    // where the "GST is never payable for multiple landlords" business rule is enforced.
    $isGstEligible = (!$isMultipleLandowners && $gstApplicableFlag === 1)
        ? true
        : false;

    $tdsSource       = (!$isMultipleLandowners) ? $singleOwner : [];
    // TDS is deducted from the ONLINE portion of the payment only (cash is never subject to TDS),
    // and governed by tdsApplicable flag inside the landowner object.
    $tdsOnlineAmount = (float)($tdsSource['onlineAmount'] ?? 0);
    $tdsAppFlag      = isset($tdsSource['tdsApplicable']) ? (int)$tdsSource['tdsApplicable'] : ($tdsOnlineAmount >= 50000 ? 1 : 0);
    $isTdsEligible   = $tdsAppFlag === 1;

    // Appraisal object may or may not include an explicit "applicable" flag — treat the object's
    // presence as the source of truth: empty appraisal ({} or missing) => nothing shown; a populated
    // object is shown unless it explicitly opts out via applicable = 0.
    $isAppraisalApplicable     = !empty($appraisal) && (!isset($appraisal['applicable']) || (int)$appraisal['applicable'] === 1);
    $isAppraisalPercentageType = $isAppraisalApplicable && ((int)($appraisal['type'] ?? 0) === 1);
    $isAppraisalFixedType      = $isAppraisalApplicable && ((int)($appraisal['type'] ?? 0) === 2);
    // Some appraisals are already baked into rentalPayment.totalRentalAmount by the backend
    // (e.g. the rent was revised months ago and this bill's base rent already reflects it) — the
    // caller flags that with appraisal.includedInCurrentRent = 1. In that case the appraisal amount
    // must NOT be added again on top of the base rent (that would double-count it); we still show
    // the appraisal type/percentage for context, just with a "already included" note instead of a
    // second add-on figure.
    $isAppraisalIncluded = $isAppraisalApplicable
        && !empty($appraisal['includedInCurrentRent'])
        && (int)$appraisal['includedInCurrentRent'] === 1;

    // ── CALCULATED NUMBERS ──
    $rentalBase          = (float)($rentalPayment['totalRentalAmount'] ?? 0);
    $appraisalAmt         = ($isAppraisalApplicable && !$isAppraisalIncluded) ? (float)($appraisal['appraisalAmount'] ?? 0) : 0;
    $totalWithAppraisal   = $rentalBase;
    $gstBaseAmount        = $rentalBase; // GST calculated on rental amount

    $rawGstPct = (float)($rentalPayment['gstPercentage'] ?? ($landOwners[0]['gstPercentage'] ?? 0));
    if ($rawGstPct > 0) {
        $gstPctVal = $rawGstPct;
    } elseif ($gst_amount_val > 0 && $rentalBase > 0) {
        $gstPctVal = round(($gst_amount_val / $rentalBase) * 100);
    } elseif ($isGstEligible) {
        $gstPctVal = 18;
    } else {
        $gstPctVal = 0;
    }

    $fallbackGstAmt       = round($gstBaseAmount * ($gstPctVal / 100), 2);
    $gstAmt               = $isGstEligible ? ($gst_amount_val > 0 ? $gst_amount_val : $fallbackGstAmt) : 0;
    if ($gst_amount_val === 0 && $gstAmt > 0) {
        $gst_amount_val = $gstAmt;
    }
    // TDS (single landlord) is calculated on the owner's ONLINE amount only, not on rental+appraisal —
    // cash is never subject to TDS. Uses the backend's own tdsPercentage, applied to the online amount.
    $tdsBaseAmount        = $tdsOnlineAmount;
    $tdsAmt               = $isTdsEligible ? round($tdsBaseAmount * (($tdsSource['tdsPercentage'] ?? 0) / 100), 2) : 0;
    // Grand Total Payable is the full amount that leaves the company's account for this payment —
    // TDS is still withdrawn from the same account (just remitted separately rather than paid to the
    // landlord), so it is NOT subtracted here. TDS is shown as an informational breakdown line only.
    $explicitNetPayable   = isset($rentalPayment['netPayableAmount']) ? (float)$rentalPayment['netPayableAmount'] : 0;
    $grandTotalPayable    = $explicitNetPayable > 0 ? $explicitNetPayable : ($rentalBase + $gstAmt);

    // Single landlord: the one owner's Payment Breakdown "Total Amount" must always agree with the
    // RENT DETAILS card's Grand Total Payable (same rule — TDS shown separately, not netted out of
    // the total). The payload's own totalAmountWithGst can't be trusted for this since some backends
    // already bake their own TDS netting into it; override with our own computed figure instead.
    // Multi-landlord is untouched — each owner's total there stays whatever the payload provided.
    if (!$isMultipleLandowners && !empty($landOwners)) {
        $landOwners[0]['total_with_gst'] = fmtAmount($grandTotalPayable);
    }

    // Finalize display flags on the computed amount, not just the raw eligibility check — a GST/TDS
    // percentage of 0 still passes eligibility but deducts nothing, so there's nothing worth showing
    // anywhere (calc-card row, Notes line, RENTAL PAYMENT SUMMARY row).
    $isGstApplicable = $isGstEligible && $gstAmt > 0;
    $isTdsApplicable = $isTdsEligible && $tdsAmt > 0;

    // GST invoice PDF — mandatory whenever GST is applicable (business rule). See
    // resolveGstInvoice() docblock for why a missing invoice is flagged rather than rejected.
    $gstInvoice = resolveGstInvoice($rentalPayment, $isGstApplicable);

    // Whether the Notes box has anything to show at all — for both single and multi landlord, a note
    // is worth showing only when GST/TDS is genuinely applicable (non-zero), appraisal applies, or
    // there's a hold/missing-invoice to flag. No more guaranteed "GST: Not applicable." filler line.
    $hasNotes = $isAppraisalApplicable
        || $isGstApplicable
        || $isTdsApplicable
        || $gstInvoice['missing']
        || (!$isMultipleLandowners && $isGstApplicable && $isGstHold)
        || (!$isMultipleLandowners && $isTdsApplicable && $isTdsHold)
        || ($isMultipleLandowners && !empty($multiTdsHoldNotes));

    $payload = [
        // RENTAL PAYMENT SUMMARY (new table, shown above Media Details)
        'rp_total_rental'           => fmtAmount($rentalPayment['totalRentalAmount']        ?? 0),
        'rp_gst_applicable'         => mapVal($GST_TDS_APPLICABLE, $gstApplicableFlag),
        'rp_gst_number'             => $gstNumberVal,
        'rp_payment_frequency'      => mapVal($PAYMENT_FREQUENCY, $rentalPayment['paymentFrequency'] ?? null),
        'rp_last_bill_date'         => fmtDate($rentalPayment['lastBillPaidDate']           ?? ''),
        'rp_current_bill_date'      => fmtDate($rentalPayment['currentBillPaidDate']        ?? ''),
        // Computed eligibility (₹50k online threshold), not the backend's raw tdsApplicable flag —
        // must agree with the TDS row/amount shown elsewhere (calc column, notes).
        'rp_tds_applicable'         => mapVal($GST_TDS_APPLICABLE, $isTdsApplicable ? 1 : 0),
        'rp_gst_percentage'         => $gstPctVal,
        'rp_gst_amount'             => fmtAmount($gst_amount_val),
        'rp_tds_percentage'         => $tdsSource['tdsPercentage']                          ?? 0,
        'rp_tds_amount'             => fmtAmount($tdsSource['tdsAmount']                    ?? 0),
        'rp_net_payable'            => fmtAmount($grandTotalPayable),
        'rp_next_bill_date'         => fmtDate($rentalPayment['nextBillingDate']            ?? ''),
        'rp_appraisal_applicable'   => mapVal($GST_TDS_APPLICABLE, $isAppraisalApplicable ? 1 : 0),
        'rp_appraisal_type'         => mapVal($APPRAISAL_TYPE, $appraisal['type']           ?? null),
        'rp_appraisal_percentage'   => $appraisal['percentage']                             ?? 0,
        'rp_appraisal_fixed_amount' => fmtAmount($appraisal['fixedAmount']                  ?? 0),
        'rp_appraisal_frequency'    => mapVal($APPRAISAL_FREQUENCY, $appraisal['frequency'] ?? null),
        'rp_appraisal_amount'       => fmtAmount($appraisal['appraisalAmount']              ?? 0),
        'rp_total_appraisal_amount' => fmtAmount($appraisal['totalAppraisalAmount']         ?? 0),
        'rp_last_appraisal_date'    => fmtDate($appraisal['lastAppraisalDate']              ?? ''),
        'rp_next_appraisal_date'    => fmtDate($appraisal['nextAppraisalDate']              ?? ''),
        // CONDITIONAL FLAGS + INTRO TEXT DATA
        'is_gst_applicable'            => $isGstApplicable,
        'is_tds_applicable'            => $isTdsApplicable,
        'is_appraisal_applicable'      => $isAppraisalApplicable,
        'is_appraisal_percentage_type' => $isAppraisalPercentageType,
        'is_appraisal_fixed_type'      => $isAppraisalFixedType,
        'is_appraisal_included_in_rent'=> $isAppraisalIncluded,
        'appraisal_display'            => $isAppraisalApplicable ? (((float)($appraisal['appraisalAmount'] ?? ($appraisal['fixedAmount'] ?? 0)) > 0) ? ('₹ ' . fmtAmount($appraisal['appraisalAmount'] ?? ($appraisal['fixedAmount'] ?? 0))) : (!empty($appraisal['percentage']) ? ($appraisal['percentage'] . '%') : 'Yes')) : 'No',
        'rp_rental_base'               => fmtAmount($rentalBase),
        'rp_appraisal_amt_calc'        => fmtAmount($appraisalAmt),
        'rp_total_with_appraisal'      => fmtAmount($totalWithAppraisal),
        'rp_gst_amt_calc'              => fmtAmount($gstAmt),
        'rp_tds_base_amount'           => fmtAmount($tdsBaseAmount),
        'rp_tds_amt_calc'              => fmtAmount($tdsAmt),
        'rp_grand_total_payable'       => fmtAmount($grandTotalPayable),
        'rp_gst_pct_val'               => $gstPctVal,

        // GST INVOICE (mandatory attachment when GST is applicable)
        'gst_invoice_attached'      => $gstInvoice['attached'],
        'gst_invoice_missing'       => $gstInvoice['missing'],

        // MEDIA DETAILS
        'media_code'        => $media['mediaCode']          ?? '',
        'media_name'        => $media['mediaName']          ?? '',
        'media_type'        => $media['mediaType']          ?? '',
        'state'             => $media['state']              ?? '',
        'city'              => $media['city']               ?? '',
        'location'          => $media['location']           ?? '',
        'full_address'      => $media['fullAddress']        ?? '',
        'width_ft'          => $media['width']              ?? '',
        'height_ft'         => $media['height']             ?? '',
        'total_sqft'        => $media['totalSqFt']          ?? '',
        'num_land_owners'   => $media['numberOfLandOwners'] ?? '',
        'media_status'      => mapVal($MEDIA_STATUS, $media['status'] ?? null),

        // AGREEMENT DETAILS
        'agree_start_date'  => fmtDate($agreement['startDate']            ?? ''),
        'agree_end_date'    => fmtDate($agreement['endDate']              ?? ''),
        'reminder_days'     => $agreement['reminderBeforeExpiry']         ?? '',
        'advance_rent'      => fmtAmount($agreement['advanceRent']        ?? 0),
        'total_rental_amt'  => fmtAmount($rentalPayment['totalRentalAmount'] ?? 0),
        'payment_frequency'   => mapVal($PAYMENT_FREQUENCY, $rentalPayment['paymentFrequency'] ?? null),
        'last_bill_date'      => fmtDate($rentalPayment['lastBillPaidDate'] ?? ''),
        'current_bill_date'   => fmtDate($rentalPayment['currentBillPaidDate'] ?? ''),
        'next_billing_date'   => fmtDate($rentalPayment['nextBillingDate'] ?? ''),
        // GST is rental-level and single-landlord-only (never shown for multiple landlords, see
        // GST business rule) — the template guards the GST rows with !is_multiple_landowners,
        // these just carry the real values through so that guard has something to show.
        'gst_applicable'      => mapVal($GST_TDS_APPLICABLE, isset($rentalPayment['gstApplicable']) ? (int)$rentalPayment['gstApplicable'] : null),
        'gst_applicable_flag' => $gstApplicableFlag,
        'gst_amount'          => $gst_amount_val > 0 ? fmtAmount($gst_amount_val) : '',
        'gst_number'          => $rentalPayment['gstNumber']  ?? '',
        // TDS applicability here follows the same ₹50k-online-threshold rule as everywhere else
        // (see TDS business rule) — never the payload's own tdsApplicable flag, and never sourced
        // from rentalPayment (it doesn't carry real TDS fields).
        'tds_applicable'      => mapVal($GST_TDS_APPLICABLE, $isTdsApplicable ? 1 : 0),
        'agree_status'        => mapVal($AGREEMENT_STATUS, $agreement['status'] ?? null),

        // LAND OWNER & PAYMENT DETAILS
        'is_multiple_landowners' => $isMultipleLandowners,
        'is_gst_hold'             => $isGstHold,
        'is_tds_hold'             => $isTdsHold,
        'land_owners'             => $landOwners,
        'multi_tds_hold_notes'    => $multiTdsHoldNotes,
        'multi_tds_deducted_rows' => $multiTdsDeductedRows,
        'has_notes'               => $hasNotes,

        // TOTAL
        'grand_total'       => $grand_total,

        // MULTI-SITE FLAG (always false here — the multi-site consolidated
        // template branch is populated by buildMultiSitePayload() instead)
        'is_multi_site'     => false,
        // banner image is static — set inside templates/cmdapproval-mail.php, not passed here
    ];

    return [
        'payload'     => $payload,
        'proof_urls'  => $proofOfCampaignUrls,
        'invoice_urls'=> $gstInvoice['attached'] ? [$gstInvoice['url']] : [],
    ];
}

// ─────────────────────────────────────────
// Build the consolidated payload for a MULTI-SITE bill: a site-wise rent/GST
// breakdown table (GST columns only shown when there is exactly one landlord
// — GST is never payable across multiple landlords) + the same "Landlord
// Details" / "TDS Deduction Summary" sections as the single-site flow, fed
// by the same processLandOwners() so TDS keeps using the current per-landlord
// logic regardless of how many sites are involved.
// ─────────────────────────────────────────
function buildMultiSitePayload(array $sites, array $topLandOwners, array $topMedia = []) {
    global $GST_TDS_APPLICABLE, $PAYMENT_FREQUENCY, $AGREEMENT_STATUS, $MEDIA_STATUS;

    $owners = processLandOwners($topLandOwners);
    $isMultipleLandowners = $owners['is_multiple_landowners'];

    $topRentalPayment = $topMedia['rentalPayment'] ?? ($sites[0]['rentalPayment'] ?? []);
    $topAgreement     = $topMedia['agreement']     ?? ($sites[0]['agreement']     ?? []);

    if (empty($topMedia['agreement']['advanceRent'])) {
        $sumAdvance = 0;
        foreach ($sites as $site) {
            $sumAdvance += (float)($site['agreement']['advanceRent'] ?? 0);
        }
        if ($sumAdvance > 0) {
            $topAgreement['advanceRent'] = $sumAdvance;
        }
    }
    $topTotalRentExplicit = isset($topMedia['rentalPayment']['totalRentalAmount'])
        ? (float)$topMedia['rentalPayment']['totalRentalAmount']
        : 0;

    $numSites    = count($sites);
    $perSiteRent = ($numSites > 0 && $topTotalRentExplicit > 0) ? ($topTotalRentExplicit / $numSites) : 0;

    $topGstApplicable = (int)($topRentalPayment['gstApplicable'] ?? 0);
    $topGstTotal      = (float)($topRentalPayment['gstAmount'] ?? 0);
    if ($topGstTotal === 0 && $topGstApplicable === 1 && $topTotalRentExplicit > 0) {
        $topGstTotal = round($topTotalRentExplicit * (($topRentalPayment['gstPercentage'] ?? 18) / 100), 2);
    }
    $perSiteGst       = ($numSites > 0 && $topGstTotal > 0) ? ($topGstTotal / $numSites) : 0;

    $siteRows             = [];
    $totalRentAmount      = 0;
    $totalAppraisalAmount = 0;
    $totalRawAppraisalAmount = 0;
    $totalGstAmount       = 0;
    $proofUrls            = [];
    $invoiceUrls          = [];
    if (!empty($topRentalPayment['gstInvoiceUrl'])) {
        $invoiceUrls[] = $topRentalPayment['gstInvoiceUrl'];
    }
    $anyGstInvoiceMissing = false;

    $topAppraisal           = $topMedia['appraisal'] ?? [];
    $topAppraisalApplicable = !empty($topAppraisal) && (!isset($topAppraisal['applicable']) || (int)$topAppraisal['applicable'] === 1);
    $topAppraisalIncluded   = $topAppraisalApplicable && !empty($topAppraisal['includedInCurrentRent']) && (int)$topAppraisal['includedInCurrentRent'] === 1;
    $topAppraisalRawAmt     = $topAppraisalApplicable ? (float)($topAppraisal['appraisalAmount'] ?? ($topAppraisal['fixedAmount'] ?? 0)) : 0;

    $anyAppraisalApplicable = $topAppraisalApplicable;
    $anyAppraisalIncluded   = $topAppraisalIncluded;

    $hasAnyExplicitSiteAppraisal = false;
    foreach ($sites as $site) {
        if (!empty($site['appraisal'])) {
            $hasAnyExplicitSiteAppraisal = true;
            break;
        }
    }

    foreach ($sites as $site) {
        $siteRentalPayment = $site['rentalPayment'] ?? [];
        $siteBaseRent      = isset($siteRentalPayment['totalRentalAmount'])
            ? (float)$siteRentalPayment['totalRentalAmount']
            : ($perSiteRent > 0 ? $perSiteRent : 0);

        $siteAppraisal            = !empty($site['appraisal']) ? $site['appraisal'] : $topAppraisal;
        $siteAppraisalApplicable  = !empty($siteAppraisal) && (!isset($siteAppraisal['applicable']) || (int)$siteAppraisal['applicable'] === 1);
        $siteAppraisalIncluded    = $siteAppraisalApplicable
            && !empty($siteAppraisal['includedInCurrentRent'])
            && (int)$siteAppraisal['includedInCurrentRent'] === 1;
        $siteAppraisalRawAmt      = $siteAppraisalApplicable ? (float)($siteAppraisal['appraisalAmount'] ?? ($siteAppraisal['fixedAmount'] ?? 0)) : 0;
        $siteAppraisalAmt         = ($siteAppraisalApplicable && !$siteAppraisalIncluded) ? $siteAppraisalRawAmt : 0;

        if ($hasAnyExplicitSiteAppraisal) {
            $totalRawAppraisalAmount += $siteAppraisalRawAmt;
            $totalAppraisalAmount    += $siteAppraisalAmt;
        }

        if ($siteAppraisalApplicable) {
            $anyAppraisalApplicable = true;
        }
        if ($siteAppraisalIncluded) {
            $anyAppraisalIncluded = true;
        }
        if ($siteAppraisalApplicable) {
            $siteAppraisalDisplay = $siteAppraisalIncluded ? 'Included in Rent' : ($siteAppraisalRawAmt > 0 ? ('₹ ' . fmtAmount($siteAppraisalRawAmt)) : 'Yes');
        } else {
            $siteAppraisalDisplay = '–';
        }

        $siteGstAppVal = isset($siteRentalPayment['gstApplicable']) ? (int)$siteRentalPayment['gstApplicable'] : null;
        if ($siteGstAppVal === 1) {
            $siteGstAmount = (float)($siteRentalPayment['gstAmount'] ?? round($siteGstBaseAmount * (($siteRentalPayment['gstPercentage'] ?? $topRentalPayment['gstPercentage'] ?? 18) / 100), 2));
        } elseif ($siteGstAppVal === 0) {
            $siteGstAmount = 0;
        } else {
            if (!$isMultipleLandowners && ($topGstApplicable === 1 || $owners['is_gst_hold'])) {
                $siteGstAmount = (float)($siteRentalPayment['gstAmount'] ?? ($perSiteGst > 0 ? $perSiteGst : round($siteGstBaseAmount * (($topRentalPayment['gstPercentage'] ?? 18) / 100), 2)));
            } else {
                $siteGstAmount = (float)($siteRentalPayment['gstAmount'] ?? 0);
            }
        }
        $siteGstApplicableFlag = $siteGstAmount > 0 ? 1 : 0;
        $siteNetPayable = $siteBaseRent + $siteAppraisalAmt + $siteGstAmount;

        $siteGstInvoice = resolveGstInvoice(!empty($siteRentalPayment) ? $siteRentalPayment : $topRentalPayment, $siteGstApplicableFlag === 1 && $siteGstAmount > 0);
        if ($siteGstInvoice['missing']) {
            $anyGstInvoiceMissing = true;
        }
        if ($siteGstInvoice['attached'] && !in_array($siteGstInvoice['url'], $invoiceUrls)) {
            $invoiceUrls[] = $siteGstInvoice['url'];
        }

        $sName = $site['mediaName'] ?? '';
        $sLoc  = $site['location'] ?? ($site['city'] ?? '');
        if (!empty($sName) && !empty($sLoc) && strcasecmp(trim($sName), trim($sLoc)) !== 0) {
            $siteNameDisplay = $sName . ' / ' . $sLoc;
        } elseif (!empty($sName)) {
            $siteNameDisplay = $sName;
        } else {
            $siteNameDisplay = $sLoc ?: '–';
        }

        $siteRows[] = [
            'site_code'             => $site['mediaCode'] ?? '',
            'site_name'             => $siteNameDisplay,
            'rent_amount'           => $siteBaseRent > 0 ? fmtAmount($siteBaseRent) : '–',
            'appraisal_display'     => $siteAppraisalDisplay,
            'gst_applicable'        => mapVal($GST_TDS_APPLICABLE, $siteGstApplicableFlag),
            'gst_amount'            => $siteGstAmount > 0 ? ('₹ ' . fmtAmount($siteGstAmount)) : '₹ 0',
            'gst_invoice_attached'  => $siteGstInvoice['attached'],
            'gst_invoice_missing'   => $siteGstInvoice['missing'],
            'net_payable'           => $siteNetPayable > 0 ? fmtAmount($siteNetPayable) : '–',
        ];

        $totalRentAmount += $siteBaseRent;
        if (!$hasAnyExplicitSiteAppraisal) {
            // Do not accumulate per-site for top-level appraisal to avoid double-counting
        } else {
            // Already accumulated above
        }
        $totalGstAmount  += $siteGstAmount;

        $siteProofUrls = $site['proof_of_campaign'] ?? [];
        if (!is_array($siteProofUrls)) {
            $siteProofUrls = !empty($siteProofUrls) ? [$siteProofUrls] : [];
        }
        $proofUrls = array_merge($proofUrls, $siteProofUrls);
    }
    $proofUrls   = array_values($proofUrls);
    $invoiceUrls = array_values(array_unique($invoiceUrls));

    if (!$hasAnyExplicitSiteAppraisal && $topAppraisalApplicable) {
        $totalRawAppraisalAmount = $topAppraisalRawAmt;
        $totalAppraisalAmount    = $topAppraisalIncluded ? 0 : $topAppraisalRawAmt;
    }

    if ($topTotalRentExplicit > 0) {
        $totalRentAmount = $topTotalRentExplicit;
    }
    $landownerGstTotal = 0;
    foreach ($topLandOwners as $lo) {
        $landownerGstTotal += (float)($lo['gstAmount'] ?? 0);
    }
    if ($landownerGstTotal > 0) {
        $totalGstAmount = $landownerGstTotal;
    } else {
        if ($topGstTotal === 0 && ($topGstApplicable === 1 || $owners['is_gst_hold']) && $topTotalRentExplicit > 0) {
            $topGstTotal = round($topTotalRentExplicit * (($topRentalPayment['gstPercentage'] ?? 18) / 100), 2);
        }
        if ($topGstTotal > 0) {
            $totalGstAmount = $topGstTotal;
        } else if ($totalGstAmount === 0 && ($topGstApplicable === 1 || $owners['is_gst_hold']) && $totalRentAmount > 0) {
            $totalGstAmount = round($totalRentAmount * (($topRentalPayment['gstPercentage'] ?? 18) / 100), 2);
        }
    }

    $grandTotal = $totalRentAmount + $totalAppraisalAmount + $totalGstAmount;

    $mediaDetailsList = [];
    $agreementDetailsList = [];
    $agreementType = $topMedia['agreementType'] ?? 'single_agreement';
    $isSeparateAgreement = ($agreementType === 'separate_agreement') || (empty($topMedia['agreement']) && count($sites) > 1);

    foreach ($sites as $site) {
        $siteRentalPayment = $site['rentalPayment'] ?? [];
        $siteAgreement     = $site['agreement']     ?? [];
        $siteBaseRent      = isset($siteRentalPayment['totalRentalAmount'])
            ? (float)$siteRentalPayment['totalRentalAmount']
            : ($perSiteRent > 0 ? $perSiteRent : 0);

        $mediaDetailsList[] = [
            'media_code'    => $site['mediaCode'] ?? '',
            'media_name'    => $site['mediaName'] ?? ($site['location'] ?? ''),
            'media_type'    => $site['mediaType'] ?? ($topMedia['mediaType'] ?? 'Hoarding'),
            'state'         => $site['state'] ?? ($topMedia['state'] ?? ''),
            'city'          => $site['city'] ?? ($topMedia['city'] ?? ''),
            'location'      => $site['location'] ?? '',
            'full_address'  => $site['fullAddress'] ?? ($site['full_address'] ?? ($site['location'] ?? '')),
            'width_ft'      => $site['width'] ?? ($topMedia['width'] ?? '–'),
            'height_ft'     => $site['height'] ?? ($topMedia['height'] ?? '–'),
            'total_sqft'    => $site['totalSqFt'] ?? ($site['total_sqft'] ?? '–'),
            'num_land_owners' => count($topLandOwners),
            'media_status'  => mapVal($MEDIA_STATUS, $site['status'] ?? ($topMedia['status'] ?? 1)),
        ];

        $agreementDetailsList[] = [
            'media_code'        => $site['mediaCode'] ?? '',
            'media_name'        => $site['mediaName'] ?? ($site['location'] ?? ''),
            'agree_start_date'  => fmtDate($siteAgreement['startDate'] ?? ($topAgreement['startDate'] ?? '')),
            'agree_end_date'    => fmtDate($siteAgreement['endDate'] ?? ($topAgreement['endDate'] ?? '')),
            'reminder_days'     => $siteAgreement['reminderBeforeExpiry'] ?? ($topAgreement['reminderBeforeExpiry'] ?? ''),
            'advance_rent'      => fmtAmount($siteAgreement['advanceRent'] ?? ($topAgreement['advanceRent'] ?? 0)),
            'total_rental_amt'  => fmtAmount($siteBaseRent),
            'payment_frequency' => mapVal($PAYMENT_FREQUENCY, $siteRentalPayment['paymentFrequency'] ?? ($topRentalPayment['paymentFrequency'] ?? 1)),
            'last_bill_date'    => fmtDate($siteRentalPayment['lastBillPaidDate'] ?? ($topRentalPayment['lastBillPaidDate'] ?? '')),
            'current_bill_date' => fmtDate($siteRentalPayment['currentBillPaidDate'] ?? ($topRentalPayment['currentBillPaidDate'] ?? '')),
            'next_billing_date' => fmtDate($siteRentalPayment['nextBillingDate'] ?? ($topRentalPayment['nextBillingDate'] ?? '')),
            'agree_status'      => mapVal($AGREEMENT_STATUS, $siteAgreement['status'] ?? ($topAgreement['status'] ?? 3)),
        ];
    }

    $tdsSource       = (!$isMultipleLandowners) ? ($topLandOwners[0] ?? []) : [];
    $tdsOnlineAmount = (float)($tdsSource['onlineAmount'] ?? 0);
    if ($tdsOnlineAmount <= 0 && !$isMultipleLandowners) {
        $tdsOnlineAmount = $totalRentAmount;
    }
    $tdsAppFlag      = isset($tdsSource['tdsApplicable']) ? (int)$tdsSource['tdsApplicable'] : ($tdsOnlineAmount >= 50000 ? 1 : 0);
    $isTdsEligible   = $tdsAppFlag === 1;
    $tdsPct          = isset($tdsSource['tdsPercentage']) ? (float)$tdsSource['tdsPercentage'] : 10;
    $tdsAmt          = $isTdsEligible ? round($tdsOnlineAmount * ($tdsPct / 100), 2) : 0;
    $isMultiTdsApp   = !$isMultipleLandowners && $isTdsEligible && $tdsAmt > 0;

    $payload = [
        'is_multi_site'                 => true,
        'is_separate_agreement'         => $isSeparateAgreement,
        'sites'                         => $siteRows,
        'media_details_list'            => $mediaDetailsList,
        'agreement_details_list'        => $agreementDetailsList,
        'show_appraisal_column'         => $anyAppraisalApplicable,
        'show_gst_column'               => !$isMultipleLandowners && ($totalGstAmount > 0 || $topGstApplicable === 1 || $owners['is_gst_hold']),
        'is_appraisal_included_in_rent' => $anyAppraisalIncluded,
        'rp_gst_pct_val'                => $topRentalPayment['gstPercentage'] ?? 18,
        'total_rent_amount'             => fmtAmount($totalRentAmount),
        'total_appraisal_amount'        => fmtAmount($totalAppraisalAmount),
        'total_appraisal_amount_raw'    => fmtAmount($totalRawAppraisalAmount),
        'total_gst_amount'              => fmtAmount($totalGstAmount),
        'total_tds_amount'              => fmtAmount($tdsAmt),
        'rp_tds_percentage'             => $tdsPct,
        'is_tds_applicable'             => $isMultiTdsApp,
        'grand_total'                   => fmtAmount($grandTotal),
        'gst_invoice_missing_any'       => $anyGstInvoiceMissing,

        // Media & Agreement Details for multi-site
        'media_code'         => 'Multi-Site (' . count($sites) . ' Sites)',
        'media_name'         => $topMedia['mediaName'] ?? 'Consolidated Multi-Site Media',
        'media_type'         => $topMedia['mediaType'] ?? 'Multiple',
        'state'              => $topMedia['state'] ?? '',
        'city'               => $topMedia['city'] ?? '',
        'location'           => $topMedia['location'] ?? 'Multiple Locations',
        'full_address'       => $topMedia['fullAddress'] ?? 'Multiple Site Locations',
        'width_ft'           => $topMedia['width'] ?? '–',
        'height_ft'          => $topMedia['height'] ?? '–',
        'total_sqft'         => $topMedia['totalSqFt'] ?? '–',
        'num_land_owners'    => count($topLandOwners),
        'media_status'       => mapVal($MEDIA_STATUS, $topMedia['status'] ?? 1),

        'agree_start_date'   => fmtDate($topAgreement['startDate'] ?? ''),
        'agree_end_date'     => fmtDate($topAgreement['endDate'] ?? ''),
        'reminder_days'      => $topAgreement['reminderBeforeExpiry'] ?? '',
        'advance_rent'       => fmtAmount($topAgreement['advanceRent'] ?? 0),
        'total_rental_amt'   => fmtAmount($totalRentAmount),
        'payment_frequency'  => mapVal($PAYMENT_FREQUENCY, $topRentalPayment['paymentFrequency'] ?? 1),
        'last_bill_date'     => fmtDate($topRentalPayment['lastBillPaidDate'] ?? ''),
        'current_bill_date'  => fmtDate($topRentalPayment['currentBillPaidDate'] ?? ''),
        'next_billing_date'  => fmtDate($topRentalPayment['nextBillingDate'] ?? ''),
        'gst_applicable'     => mapVal($GST_TDS_APPLICABLE, $topGstApplicable),
        'gst_applicable_flag'=> $topGstApplicable,
        'gst_amount'         => $topGstTotal > 0 ? fmtAmount($topGstTotal) : '',
        'gst_number'         => $topRentalPayment['gstNumber'] ?? '',
        'tds_applicable'     => 'No',
        'agree_status'       => mapVal($AGREEMENT_STATUS, $topAgreement['status'] ?? 3),

        'is_multiple_landowners'  => $isMultipleLandowners,
        'is_gst_hold'             => $owners['is_gst_hold'],
        'is_tds_hold'             => $owners['is_tds_hold'],
        'land_owners'             => $owners['land_owners'],
        'multi_tds_hold_notes'    => $owners['multi_tds_hold_notes'],
        'multi_tds_deducted_rows' => $owners['multi_tds_deducted_rows'],
        'has_notes'               => !empty($owners['multi_tds_hold_notes']) || $anyGstInvoiceMissing || $owners['is_gst_hold'],
    ];

    return ['payload' => $payload, 'proof_urls' => $proofUrls, 'invoice_urls' => $invoiceUrls];
}

// ─────────────────────────────────────────
// For separate_bill + single landlord + multiple sites: split that one
// owner's aggregate share/online/cash amounts down to just this site's
// portion, so each separate email's "Landlord Details" card reflects only
// that site's payment. Prefers an explicit per-site online/cash split if the
// caller provides one; otherwise prorates the owner's overall online/cash
// mix by this site's share of the total (siteRentBreakdown[].shareAmount).
// ─────────────────────────────────────────
function scopeOwnerToSite(array $owner, string $siteCode) {
    $breakdown = null;
    foreach (($owner['siteRentBreakdown'] ?? []) as $entry) {
        if (($entry['mediaCode'] ?? null) === $siteCode) {
            $breakdown = $entry;
            break;
        }
    }
    if ($breakdown === null) {
        return $owner; // no per-site data available — fall back to the owner as given
    }

    $scoped = $owner;
    unset($scoped['siteRentBreakdown']);

    if (isset($breakdown['onlineAmount']) || isset($breakdown['cashAmount'])) {
        $scoped['onlineAmount'] = $breakdown['onlineAmount'] ?? 0;
        $scoped['cashAmount']   = $breakdown['cashAmount']   ?? 0;
        $scoped['shareAmount']  = $breakdown['shareAmount']  ?? ($scoped['onlineAmount'] + $scoped['cashAmount']);
    } else {
        $siteShare  = (float)($breakdown['shareAmount'] ?? 0);
        $totalShare = (float)($owner['shareAmount'] ?? 0);
        $ratio      = $totalShare > 0 ? ($siteShare / $totalShare) : 0;
        $scoped['shareAmount']  = $siteShare;
        $scoped['onlineAmount'] = round((float)($owner['onlineAmount'] ?? 0) * $ratio, 2);
        $scoped['cashAmount']   = round((float)($owner['cashAmount']   ?? 0) * $ratio, 2);
    }
    $scoped['totalAmountWithGst'] = null; // recomputed fresh for this site inside buildSingleMediaPayload

    return $scoped;
}
