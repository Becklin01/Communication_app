<?php
$sessionPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'sessions';
if (!is_dir($sessionPath)) {
    mkdir($sessionPath, 0777, true);
}

if (session_status() === PHP_SESSION_NONE) {
    session_save_path($sessionPath);
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_NAME', 'communication_app');
define('DB_USER', 'root');
define('DB_PASS', '');

function connectToServer(?string $database = null): PDO
{
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    if ($database !== null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . $database . ";charset=utf8mb4";
    }

    return new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
}

function ensureDatabaseSchema(PDO $serverPdo): void
{
    $serverPdo->exec(
        "CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );
}

function seedDemoUsers(PDO $pdo): void
{
    $count = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($count > 0) {
        return;
    }

    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)");
    $stmt->execute(['person1', password_hash('password123', PASSWORD_DEFAULT), 'Person One']);
    $stmt->execute(['person2', password_hash('password123', PASSWORD_DEFAULT), 'Person Two']);
}

function ensureApplicationTables(PDO $pdo): void
{
    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB"
    );

    $pdo->exec(
        "CREATE TABLE IF NOT EXISTS messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB"
    );
}

function sanitizeInput(string $value): string
{
    return trim($value);
}

function validateUsername(string $username): bool
{
    return (bool) preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $username);
}

function validatePassword(string $password): bool
{
    return strlen($password) >= 6 && strlen($password) <= 255;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && is_string($token) && hash_equals($_SESSION['csrf_token'], $token);
}

function requireLogin(): void
{
    if (!isset($_SESSION['user_id'])) {
        header('Location: index.php?error=login_required');
        exit();
    }
}

try {
    $serverPdo = connectToServer();
    ensureDatabaseSchema($serverPdo);
    $pdo = connectToServer(DB_NAME);
    ensureApplicationTables($pdo);
    seedDemoUsers($pdo);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Please check your MySQL service.');
}
?>
