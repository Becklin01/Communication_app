<?php
require_once 'config/database.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$senderId = (int) $_SESSION['user_id'];
$receiverId = isset($data['receiver_id']) ? (int) $data['receiver_id'] : 0;
$message = trim((string) ($data['message'] ?? ''));

if ($receiverId <= 0 || $receiverId === $senderId) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Invalid recipient']);
    exit();
}

if ($message === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
    exit();
}

$userStmt = $pdo->prepare("SELECT id FROM users WHERE id = ?");
$userStmt->execute([$receiverId]);
if (!$userStmt->fetch()) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Recipient not found']);
    exit();
}

$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$stmt->execute([$senderId, $receiverId, $message]);

echo json_encode(['success' => true]);
?>
