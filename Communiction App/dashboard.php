<?php
require_once 'config/database.php';
requireLogin();

$userId = (int) $_SESSION['user_id'];

$usersStmt = $pdo->prepare("SELECT id, username, full_name FROM users WHERE id != ? ORDER BY full_name ASC");
$usersStmt->execute([$userId]);
$otherUsers = $usersStmt->fetchAll();

$selectedUserId = isset($_GET['user']) ? (int) $_GET['user'] : 0;
if ($selectedUserId <= 0 && !empty($otherUsers)) {
    $selectedUserId = (int) $otherUsers[0]['id'];
}

$selectedUser = null;
foreach ($otherUsers as $user) {
    if ((int) $user['id'] === $selectedUserId) {
        $selectedUser = $user;
        break;
    }
}

if ($selectedUser !== null) {
    $markReadStmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE receiver_id = ? AND sender_id = ?");
    $markReadStmt->execute([$userId, $selectedUserId]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Communication App Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="dashboard-shell">
        <aside class="sidebar">
            <div>
                <p class="eyebrow">Signed In</p>
                <h1><?php echo htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <p class="sidebar-username">@<?php echo htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>

            <div class="sidebar-users">
                <h2>Chats</h2>
                <?php if (empty($otherUsers)): ?>
                    <p class="empty-copy">No other users yet. Register a second account to start chatting.</p>
                <?php else: ?>
                    <?php foreach ($otherUsers as $chatUser): ?>
                        <a
                            href="dashboard.php?user=<?php echo (int) $chatUser['id']; ?>"
                            class="chat-user <?php echo ((int) $chatUser['id'] === $selectedUserId) ? 'active' : ''; ?>"
                        >
                            <span><?php echo htmlspecialchars($chatUser['full_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <small>@<?php echo htmlspecialchars($chatUser['username'], ENT_QUOTES, 'UTF-8'); ?></small>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <a href="logout.php" class="logout-btn">Logout</a>
        </aside>

        <main class="chat-layout">
            <?php if ($selectedUser === null): ?>
                <section class="empty-state-card">
                    <h2>Your chat dashboard is ready</h2>
                    <p>Create another user account from the landing page, then come back here to start messaging.</p>
                </section>
            <?php else: ?>
                <div class="chat-header">
                    <div>
                        <p class="eyebrow">Conversation</p>
                        <h2><?php echo htmlspecialchars($selectedUser['full_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="sidebar-username">@<?php echo htmlspecialchars($selectedUser['username'], ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
                <div class="messages-area" id="messagesArea"></div>
                <div class="message-input">
                    <textarea id="messageText" placeholder="Type your message here..." rows="3"></textarea>
                    <button type="button" onclick="sendMessage()">Send</button>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php if ($selectedUser !== null): ?>
        <script>
            const userId = <?php echo $userId; ?>;
            const receiverId = <?php echo $selectedUserId; ?>;
        </script>
        <script src="js/script.js"></script>
    <?php endif; ?>
</body>
</html>
