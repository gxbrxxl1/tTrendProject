<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Create a log function for better debugging
function log_error($message) {
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents(__DIR__ . '/debug.log', "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

// Log all request details
log_error("Request received: " . json_encode([
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'get' => $_GET,
    'post' => $_POST,
    'raw' => file_get_contents('php://input')
]));

// Log server information
log_error("Server information: " . json_encode([
    'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
    'server_port' => $_SERVER['SERVER_PORT'] ?? 'unknown',
    'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'unknown'
]));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_error("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Try to get the query from POST or raw input
$query = $_POST['query'] ?? '';
if (empty($query)) {
    $input = json_decode(file_get_contents('php://input'), true);
    $query = $input['query'] ?? '';
}

if (empty($query)) {
    log_error("Empty query");
    echo json_encode([
        'success' => false,
        'message' => 'Query is required'
    ]);
    exit;
}

// Get the absolute path to the Python script
$pythonScript = __DIR__ . '/tech_ai.py';
log_error("Python script path: $pythonScript");

// Check if the Python script exists
if (!file_exists($pythonScript)) {
    log_error("ERROR: Python script does not exist at path: $pythonScript");
    echo json_encode([
        'success' => false,
        'message' => 'Error: Python script not found'
    ]);
    exit;
}

// Escape the query for shell execution
$escapedQuery = escapeshellarg($query);
log_error("Query: $query");
log_error("Escaped query: $escapedQuery");

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
            log_error("Found Python at: $pythonPath");
            break;
        }
    }
}
log_error("Python path: $pythonPath");

// Check for .env file
$envFile = __DIR__ . '/.env';
if (!file_exists($envFile)) {
    log_error("WARNING: .env file not found at: $envFile");
}

// Execute the Python script with error output
$command = sprintf('"%s" "%s" %s 2>&1', $pythonPath, $pythonScript, $escapedQuery);
log_error("Executing command: $command");

// Execute with output
$output = shell_exec($command);
log_error("Raw output: " . ($output ?? "NULL"));

// Log errors if any
if ($output === null) {
    $error = error_get_last();
    log_error('Error executing Python script: ' . ($error ? json_encode($error) : 'Unknown error'));
    echo json_encode([
        'success' => false,
        'message' => 'Error processing request: Python script execution failed'
    ]);
    exit;
}

// Check for Python errors in the output
if (strpos($output, 'Traceback') !== false || strpos($output, 'Error:') !== false) {
    log_error("Python error detected: $output");
    echo json_encode([
        'success' => false,
        'message' => 'Python script error: ' . $output
    ]);
    exit;
}

// Try to decode the JSON response
$result = json_decode($output, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    log_error('Error decoding JSON: ' . json_last_error_msg());
    log_error('Raw output: ' . $output);
    echo json_encode([
        'success' => false,
        'message' => 'Error processing response: Invalid JSON output',
        'debug_output' => $output
    ]);
    exit;
}

log_error('Sending response: ' . json_encode($result));
echo json_encode($result); 