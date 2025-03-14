<?php
// Test script to directly execute the Python script

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing direct Python execution...\n";

// Define the query
$query = "What is the best smartphone in 2024?";
echo "Query: $query\n\n";

// Get the absolute path to the Python script
$pythonScript = __DIR__ . '/tech_ai.py';
echo "Python script path: $pythonScript\n";

// Check if the Python script exists
if (!file_exists($pythonScript)) {
    echo "ERROR: Python script does not exist at path: $pythonScript\n";
    exit(1);
}

// Escape the query for shell execution
$escapedQuery = escapeshellarg($query);
echo "Escaped query: $escapedQuery\n";

// Get the Python executable path - use full path for Windows
$pythonPath = 'python';
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    // On Windows, try to find Python in common locations
    $possiblePaths = [
        'C:\\Python313\\python.exe',
        'C:\\Python312\\python.exe',
        'C:\\Python311\\python.exe',
        'C:\\Python310\\python.exe',
        'C:\\Python39\\python.exe',
        'C:\\Python38\\python.exe',
        'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python313\\python.exe',
        'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python312\\python.exe',
        'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python311\\python.exe',
        'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python310\\python.exe',
        'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python39\\python.exe',
        'C:\\Users\\' . getenv('USERNAME') . '\\AppData\\Local\\Programs\\Python\\Python38\\python.exe',
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $pythonPath = $path;
            echo "Found Python at: $pythonPath\n";
            break;
        }
    }
}
echo "Python path: $pythonPath\n";

// Check for .env file
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    echo "WARNING: .env file not found at: $envFile\n";
}

// Execute the Python script with error output
$command = sprintf('"%s" "%s" %s 2>&1', $pythonPath, $pythonScript, $escapedQuery);
echo "Executing command: $command\n\n";

// Execute with output
$output = shell_exec($command);
echo "Raw output:\n$output\n\n";

// Try to decode the JSON response
$data = json_decode($output, true);
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