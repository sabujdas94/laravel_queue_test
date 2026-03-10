<?php

/*
|--------------------------------------------------------------------------
| Test Script for Queue Forward API
|--------------------------------------------------------------------------
|
| This script demonstrates how to use the Queue Forward API programmatically.
| Run it with: php test_api.php
|
*/

// Configuration
$baseUrl = 'http://localhost';
$apiKey = 'your-api-key-here'; // Replace with your actual API key

// Test 1: Forward a simple request
echo "Test 1: Forwarding a simple request...\n";

$ch = curl_init("$baseUrl/api/forward");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'X-API-KEY: ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'forward_url' => 'https://httpbin.org/post',
        'payload' => [
            'message' => 'Test from PHP script',
            'timestamp' => date('Y-m-d H:i:s'),
            'test_id' => rand(1000, 9999),
        ],
    ]),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response Code: $httpCode\n";
echo "Response: $response\n\n";

$responseData = json_decode($response, true);

if (isset($responseData['request_id'])) {
    $requestId = $responseData['request_id'];
    
    // Wait a bit for the queue to process
    echo "Waiting 3 seconds for queue to process...\n";
    sleep(3);
    
    // Test 2: Check status
    echo "\nTest 2: Checking request status...\n";
    
    $ch = curl_init("$baseUrl/api/status/$requestId");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'X-API-KEY: ' . $apiKey,
        ],
    ]);
    
    $statusResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "Response Code: $httpCode\n";
    echo "Response: $statusResponse\n\n";
    
    $statusData = json_decode($statusResponse, true);
    
    if (isset($statusData['status'])) {
        echo "Status: {$statusData['status']}\n";
        echo "Response Status: " . ($statusData['response_status'] ?? 'N/A') . "\n";
    }
}

// Test 3: Forward with authorization token
echo "\n\nTest 3: Forwarding with authorization token...\n";

$ch = curl_init("$baseUrl/api/forward");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'X-API-KEY: ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'forward_url' => 'https://httpbin.org/bearer',
        'token' => 'test-bearer-token-12345',
        'payload' => [
            'event' => 'test.event',
            'data' => [
                'id' => 999,
                'value' => 'test data',
            ],
        ],
    ]),
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Response Code: $httpCode\n";
echo "Response: $response\n\n";

echo "\n=== Tests Complete ===\n";
echo "Check the queue worker output to see job processing logs.\n";
