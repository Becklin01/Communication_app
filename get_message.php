<?php
require_once 'config/database.php';
requireLogin();

header('Content-Type: application/json');

$userId = (int) $_SESSION['user_id'];
$otherUserId = isset($_GET['other_user_id']) ? (int) $_GET['other_user_id'] : 0;

if ($otherUserId <= 0 || $otherUserId === $userId) {
    http_response_code(422);
    echo json_encode([]);
    exit();
}

$stmt = $pdo->prepare(
    "SELECT m.id, m.sender_id, m.receiver_id, m.message, m.is_read, m.created_at, u.full_name AS sender_name
     FROM messages m
     INNER JOIN users u ON m.sender_id = u.id
     WHERE (m.sender_id = ? AND m.receiver_id = ?)
        OR (m.sender_id = ? AND m.receiver_id = ?)
     ORDER BY m.created_at ASC, m.id ASC"
);
$stmt->execute([$userId, $otherUserId, $otherUserId, $userId]);

echo json_encode($stmt->fetchAll());
?>
