<?php
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
    header('Location: index.php?error=csrf');
    exit();
}

$fullName = sanitizeInput($_POST['full_name'] ?? '');
$username = sanitizeInput($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($fullName === '' || !validateUsername($username) || !validatePassword($password)) {
    header('Location: index.php?error=invalid_registration');
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);

    if ($stmt->fetch()) {
        header('Location: index.php?error=username_taken');
        exit();
    }

    $insert = $pdo->prepare("INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)");
    $insert->execute([$username, password_hash($password, PASSWORD_DEFAULT), $fullName]);

    header('Location: index.php?success=registered');
    exit();
} catch (PDOException $e) {
    header('Location: index.php?error=database');
    exit();
}
?>
