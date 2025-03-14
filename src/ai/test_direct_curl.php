<?php
// Simple test script to directly test the AI endpoint using cURL

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define the endpoint and query
$endpoint = 'http://localhost:8000/process_ai.php';
$query = 'What is the best smartphone in 2024?';

echo "Testing AI endpoint: $endpoint\n";
echo "Query: $query\n\n";

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['query' => $query]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Add verbose debugging
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_STDERR, $verbose);

// Execute the request
echo "Sending request...\n";
$response = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Get verbose information
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
echo "Verbose log:\n$verboseLog\n\n";

// Close cURL
curl_close($ch);

// Check for errors
if ($curlError) {
    echo "cURL Error: $curlError\n";
    exit(1);
}

if ($httpCode !== 200) {
    echo "HTTP Error: $httpCode\n";
    echo "Response: $response\n";
    exit(1);
}

// Process the response
echo "HTTP Status Code: $httpCode\n\n";
echo "Raw Response:\n$response\n\n";

// Try to decode JSON
$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error decoding JSON: " . json_last_error_msg() . "\n";
    exit(1);
}

// Display the result
echo "Decoded Response:\n";
echo "Success: " . ($data['success'] ? 'Yes' : 'No') . "\n";
echo "Message: " . $data['message'] . "\n";

if (isset($data['videos']) && is_array($data['videos'])) {
    echo "\nRelated Videos:\n";
    foreach ($data['videos'] as $video) {
        echo "- {$video['title']} (Views: {$video['views']})\n";
    }
}

echo "\nTest completed successfully!\n"; 