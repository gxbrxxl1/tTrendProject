<?php
// Simple test script to verify the AI endpoint is working

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing AI endpoint...\n";

// Define the query
$query = "What is the best smartphone in 2024?";
echo "Query: $query\n\n";

// Initialize cURL
$ch = curl_init();

// Set cURL options
curl_setopt($ch, CURLOPT_URL, "http://localhost:8000/process_ai.php");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['query' => $query]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

// Execute the request
echo "Sending request to AI endpoint...\n";
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
    exit(1);
}

// Get HTTP status code
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
echo "HTTP Status Code: $httpCode\n\n";

// Close cURL
curl_close($ch);

// Process the response
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