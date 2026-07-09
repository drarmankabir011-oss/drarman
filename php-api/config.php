<?php
/**
 * Database Configuration
 * Update these credentials with your cPanel database details
 */

// Database credentials
$db_host = 'localhost';  // Usually localhost on cPanel
$db_user = 'your_cpanel_username_dbuser';  // Create via phpMyAdmin
$db_pass = 'your_database_password';  // Set via phpMyAdmin
$db_name = 'your_cpanel_username_database';  // Create via phpMyAdmin

// Create connection
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Set charset to UTF-8
$conn->set_charset('utf8mb4');

// Enable error reporting for development (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// API Response helper
function sendResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    echo json_encode($data);
    exit();
}

// Error response helper
function sendError($message, $status = 400) {
    sendResponse(['error' => $message], $status);
}

// Success response helper
function sendSuccess($data, $message = 'Success', $status = 200) {
    sendResponse(['success' => true, 'message' => $message, 'data' => $data], $status);
}

// Auth secret — read from environment when possible
$AUTH_SECRET = getenv('AUTH_SECRET') ?: 'your-secret-key-change-in-production';

// Token helpers (simple HMAC payload tokens). Consider replacing with a JWT library in production.
function generateToken($user_id, $email, $role) {
    global $AUTH_SECRET;
    $payload = [
        'user_id' => $user_id,
        'email' => $email,
        'role' => $role,
        'iat' => time(),
        'exp' => time() + (24 * 60 * 60) // 24 hours
    ];
    $encoded = base64_encode(json_encode($payload));
    $sig = hash_hmac('sha256', $encoded, $AUTH_SECRET);
    return $encoded . '.' . $sig;
}

function verifyToken($token) {
    global $AUTH_SECRET;
    if (!$token || !is_string($token)) return false;
    $parts = explode('.', $token);
    if (count($parts) !== 2) return false;
    $payloadJson = base64_decode($parts[0]);
    if ($payloadJson === false) return false;
    $payload = json_decode($payloadJson, true);
    if (!is_array($payload)) return false;
    $signature = hash_hmac('sha256', $parts[0], $AUTH_SECRET);
    if (!hash_equals($signature, $parts[1])) return false;
    if (!isset($payload['exp']) || $payload['exp'] < time()) return false;
    return $payload;
}

// Validate Authorization header, verify token, and return decoded payload.
function validateAuth() {
    $headers = getallheaders();
    $authHeader = null;
    if ($headers) {
        foreach ($headers as $k => $v) {
            if (strtolower($k) === 'authorization') {
                $authHeader = $v;
                break;
            }
        }
    }
    if (!$authHeader) {
        sendError('Unauthorized: No authorization header', 401);
    }
    if (strpos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
        $payload = verifyToken($token);
        if (!$payload) {
            sendError('Unauthorized: Invalid or expired token', 401);
        }
        return $payload; // associative array with user_id, email, role
    }
    sendError('Unauthorized: Invalid token format', 401);
}

// Get current user role from token/session — returns role string or null
function getUserRole() {
    try {
        $payload = validateAuth();
        return $payload['role'] ?? null;
    } catch (Exception $e) {
        return null;
    }
}

// Check user permissions — accepts string or array of allowed roles. Returns payload when allowed.
function checkPermission($allowed_roles) {
    if (is_string($allowed_roles)) $allowed_roles = [$allowed_roles];
    $payload = validateAuth();
    $user_role = $payload['role'] ?? null;
    if (!in_array($user_role, $allowed_roles)) {
        sendError('Unauthorized: Insufficient permissions', 403);
    }
    return $payload;
}

?>