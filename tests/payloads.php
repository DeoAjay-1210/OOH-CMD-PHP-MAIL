<?php
/**
 * Dynamic scenario payload loader.
 *
 * Reads directly from tests/payloads.json so that any additions, edits,
 * or removals in payloads.json are automatically reflected without
 * requiring code changes in PHP files.
 */

function getScenarioPayloads(): array {
    $jsonPath = __DIR__ . '/payloads.json';

    if (!file_exists($jsonPath)) {
        throw new RuntimeException("Payloads JSON file not found at: {$jsonPath}");
    }

    $raw = file_get_contents($jsonPath);
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        throw new RuntimeException("Failed to decode JSON from {$jsonPath}");
    }

    return $data;
}
