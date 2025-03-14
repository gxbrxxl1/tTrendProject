<?php
require_once __DIR__ . '/process_ai.php';

$query = "Tell me about the latest iPhone";
$result = processAIQuery($query);
echo json_encode($result, JSON_PRETTY_PRINT); 