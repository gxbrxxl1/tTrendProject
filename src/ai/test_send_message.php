<?php
// Test script to directly test the sendMessage method in the ChatController

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start a session
session_start();

// Set a test user ID
$_SESSION['user_id'] = 1;

echo "Testing sendMessage method...\n";

// Define the test data
$conversationId = 1; // Replace with an actual conversation ID from your database
$message = "What is the best smartphone in 2024?";

echo "Conversation ID: $conversationId\n";
echo "Message: $message\n\n";

// Create the POST data
$_POST['conversation_id'] = $conversationId;
$_POST['message'] = $message;

// Include the necessary files
require_once __DIR__ . '/../../vendor/autoload.php';

// Create an instance of the ChatController
$controller = new \App\Controllers\ChatController();

// Call the sendMessage method
echo "Calling sendMessage method...\n";
ob_start();
$controller->sendMessage();
$output = ob_get_clean();

echo "Raw output:\n$output\n\n";

// Try to decode the JSON response
$data = json_decode($output, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Error decoding JSON: " . json_last_error_msg() . "\n";
    exit(1);
}

// Display the result
echo "Decoded output:\n";
echo "Success: " . ($data['success'] ? 'Yes' : 'No') . "\n";
echo "Message: " . $data['message'] . "\n";

if (isset($data['videos']) && is_array($data['videos'])) {
    echo "\nRelated Videos:\n";
    foreach ($data['videos'] as $video) {
        echo "- {$video['title']} (Views: {$video['views']})\n";
    }
}

echo "\nTest completed successfully!\n"; 