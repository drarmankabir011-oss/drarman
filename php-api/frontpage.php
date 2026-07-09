<?php
require_once 'config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action = $_GET['action'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);

// Save: requires auth
if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // validateAuth() now returns decoded payload
    $payload = validateAuth();
    if (!isset($payload['role']) || $payload['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Only admin can save front page']);
        exit();
    }

    $content = json_encode($input);
    if ($content === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO frontpage (id, content, updated_at) VALUES (1, ?, NOW()) ON DUPLICATE KEY UPDATE content = VALUES(content), updated_at = NOW()");
    $stmt->bind_param('s', $content);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Saved']);
        exit();
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'DB save failed: ' . $conn->error]);
        exit();
    }
}

// Get: return current saved front page
else if ($action === 'get' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $res = $conn->query("SELECT content FROM frontpage WHERE id = 1 LIMIT 1");
    if ($res && $row = $res->fetch_assoc()) {
        header('Content-Type: application/json');
        echo $row['content'];
        exit();
    } else {
        http_response_code(204);
        echo json_encode(['message' => 'No front page saved']);
        exit();
    }
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action or method']);
exit();
?>
