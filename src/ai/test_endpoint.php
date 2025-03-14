<?php
// Test script for the AI endpoint

// Set up cURL request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/process_ai.php');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['query' => 'What is the best smartphone in 2024?']));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Execute the request
$response = curl_exec($ch);

// Check for errors
if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
    exit;
}

curl_close($ch);

// Parse and display the response
$data = json_decode($response, true);

echo "Response Status: " . ($data['success'] ? 'Success' : 'Failed') . "\n";
echo "Message: " . ($data['message'] ?? 'No message') . "\n";

if (isset($data['videos']) && is_array($data['videos'])) {
    echo "\nVideos:\n";
    foreach ($data['videos'] as $video) {
        echo "- " . $video['title'] . " (Views: " . $video['views'] . ")\n";
    }
}

echo "\nRaw Response:\n" . $response . "\n"; 