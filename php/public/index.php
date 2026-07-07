<?php

// Enable error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set headers
header('Content-Type: application/json');
header('X-Powered-By: Dr. Arman Kabir API');

// Load environment
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/config/Environment.php';

use Config\Environment;

// Load .env file
try {
    Environment::load(__DIR__ . '/../../.env');
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration error: ' . $e->getMessage()]);
    exit;
}

// Route the request
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_GET['_url'] ?? '/', PHP_URL_PATH);
echo json_encode(['message' => 'Dr. Arman Kabir Care API', 'version' => '1.0.0']);
