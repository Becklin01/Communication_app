<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$username = sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    header('Location: index.php?error=invalid');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id, username, password, full_name FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: index.php?error=invalid');
        exit();
    }

    $storedPassword = $user['password'];
    $passwordValid = false;

    if (password_get_info($storedPassword)['algo'] !== null) {
        $passwordValid = password_verify($password, $storedPassword);
    } elseif (strlen($storedPassword) === 32 && ctype_xdigit($storedPassword)) {
        $passwordValid = md5($password) === $storedPassword;
    } else {
        $passwordValid = hash_equals($storedPassword, $password);
    }

    if (!$passwordValid) {
        header('Location: index.php?error=invalid');
        exit();
    }

    if (password_get_info($storedPassword)['algo'] === null || password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
        $rehashStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $rehashStmt->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }

    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['full_name'] = $user['full_name'];

    header('Location: dashboard.php');
    exit();
} catch (PDOException $e) {
    header('Location: index.php?error=database');
    exit();
}
?>
