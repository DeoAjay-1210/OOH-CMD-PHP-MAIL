<?php
/**
 * Dynamic, automated single-run test runner for all scenarios in tests/payloads.json.
 *
 * Automatically reads whatever scenarios are currently defined in tests/payloads.json
 * without requiring any manual PHP code updates when payloads.json changes.
 *
 * Execution options:
 * 1. Web Browser: https://adinndigital.com/api/outdoormedia/tests/run_tests.php
 * 2. Command Line: php tests/run_tests.php [optional_endpoint_url]
 */

if (isset($_SERVER['REQUEST_METHOD'])) {
    header('Content-Type: text/plain; charset=utf-8');
}

require_once __DIR__ . '/payloads.php';

// Target endpoint URL (defaults to live URL, customizable via GET/POST parameter or CLI argument)
$endpointUrl = $_GET['endpoint'] ?? $_POST['endpoint'] ?? ($argv[1] ?? 'https://adinndigital.com/api/outdoormedia/cmdApprovalSK.php');

$jsonPath = __DIR__ . '/payloads.json';

try {
    $scenarios = getScenarioPayloads();
} catch (Throwable $e) {
    echo "Error loading scenarios: " . $e->getMessage() . "\n";
    exit(1);
}

if (empty($scenarios)) {
    echo "Error: No scenarios found in {$jsonPath}.\n";
    exit(1);
}

echo "======================================================================\n";
echo "           CMD APPROVAL AUTOMATED MAIL TEST RUNNER                    \n";
echo "======================================================================\n";
echo "Target Endpoint URL : {$endpointUrl}\n";
echo "Payload Source File : " . realpath($jsonPath) . "\n";
echo "Total Scenarios     : " . count($scenarios) . "\n";
echo "======================================================================\n\n";

/**
 * Sends a payload via cURL / HTTP POST request to the target endpoint.
 */
function sendPayloadViaCurl(string $url, array $payload): array {
    $jsonData = json_encode($payload);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($jsonData)
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $responseBody = curl_exec($ch);
        $httpCode     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError    = curl_error($ch);
        curl_close($ch);

        if ($responseBody === false || !empty($curlError)) {
            throw new RuntimeException("cURL Error: " . ($curlError ?: "No response from server"));
        }
    } else {
        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n" .
                             "Content-Length: " . strlen($jsonData) . "\r\n",
                'method'  => 'POST',
                'content' => $jsonData,
                'timeout' => 60,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];
        $context = stream_context_create($options);
        $responseBody = @file_get_contents($url, false, $context);
        $httpCode = 500;
        if (isset($http_response_header) && is_array($http_response_header)) {
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $m)) {
                $httpCode = (int)$m[1];
            }
        }
        if ($responseBody === false) {
            throw new RuntimeException("HTTP Error: Unable to connect to {$url}");
        }
    }

    $decoded = json_decode($responseBody, true);

    if ($httpCode !== 200) {
        $bodySummary = strip_tags($responseBody);
        // HTML error pages (e.g. WordPress "Page not found") are summarised by their <title>
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $responseBody, $t)) {
            $bodySummary = trim(html_entity_decode($t[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'))
                . ($httpCode === 404 ? " - endpoint not found at {$url}; check that the file is deployed at this path" : '');
        }
        $msg = is_array($decoded) && isset($decoded['message'])
            ? $decoded['message']
            : "HTTP {$httpCode} Response: " . $bodySummary;
        throw new RuntimeException($msg);
    }

    if (is_array($decoded) && isset($decoded['status']) && $decoded['status'] === 'error') {
        throw new RuntimeException($decoded['message'] ?? 'Server returned error status');
    }

    return [
        'httpCode' => $httpCode,
        'message'  => is_array($decoded) && isset($decoded['message']) ? $decoded['message'] : 'Mail sent successfully',
        'response' => $decoded
    ];
}

$results = [];
$totalScenarios = count($scenarios);
$successCount = 0;
$failedCount = 0;
$currentIndex = 1;

foreach ($scenarios as $key => $payload) {
    // Dynamically extract scenario name and mailtype from payload or array key
    $scenarioName = $payload['scenario'] ?? (is_string($key) ? $key : "Scenario #{$currentIndex}");
    $mailType     = $payload['mailtype'] ?? 'cmdapproval';

    echo "[{$currentIndex}/{$totalScenarios}] Executing Scenario: {$scenarioName}\n";
    echo "    Mail Type: {$mailType}\n";

    try {
        $res = sendPayloadViaCurl($endpointUrl, $payload);
        $successCount++;
        $statusMsg = $res['message'];

        $results[] = [
            'index'        => $currentIndex,
            'name'         => $scenarioName,
            'mailtype'     => $mailType,
            'status'       => 'PASS',
            'message'      => $statusMsg,
            'errorMessage' => null
        ];

        echo "    Result: [PASS] {$statusMsg}\n\n";
    } catch (Throwable $e) {
        $failedCount++;
        $errorMsg = $e->getMessage();

        $results[] = [
            'index'        => $currentIndex,
            'name'         => $scenarioName,
            'mailtype'     => $mailType,
            'status'       => 'FAIL',
            'message'      => 'Failed to process scenario/send mail',
            'errorMessage' => $errorMsg
        ];

        echo "    Result: [FAIL] Error: {$errorMsg}\n\n";
    }

    $currentIndex++;
}

echo "======================================================================\n";
echo "                     FINAL TEST EXECUTION SUMMARY                     \n";
echo "======================================================================\n";
echo "Target Endpoint URL      : {$endpointUrl}\n";
echo "Total Scenarios Executed : {$totalScenarios}\n";
echo "Successful Mail Sends    : {$successCount}\n";
echo "Failed Scenarios         : {$failedCount}\n";
echo "----------------------------------------------------------------------\n";
echo "SUMMARY BY SCENARIO:\n";
echo "----------------------------------------------------------------------\n";

foreach ($results as $item) {
    $statusText = $item['status'] === 'PASS' ? '[PASS]' : '[FAIL]';
    echo sprintf("%2d. %-6s | Mail Type: %-12s | %s\n", $item['index'], $statusText, $item['mailtype'], $item['name']);
    if ($item['status'] === 'FAIL') {
        echo "    └─ Error Message: " . $item['errorMessage'] . "\n";
    } else {
        echo "    └─ Detail       : " . $item['message'] . "\n";
    }
}

echo "======================================================================\n";
if ($failedCount === 0) {
    echo "OVERALL RESULT: ALL SCENARIOS COMPLETED SUCCESSFULLY!\n";
} else {
    echo "OVERALL RESULT: COMPLETED WITH {$failedCount} FAILED SCENARIO(S).\n";
}
echo "======================================================================\n";

exit($failedCount > 0 ? 1 : 0);
