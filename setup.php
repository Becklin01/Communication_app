<?php
// Simple setup script
echo "<h1>Database Setup</h1>";

$host = 'localhost';
$username = 'root';
$password = '';

try {
    // Connect to MySQL
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color:green'>✓ Connected to MySQL successfully!</p>";
    
    // Create database
    $pdo->exec("DROP DATABASE IF EXISTS communication_app");
    $pdo->exec("CREATE DATABASE communication_app");
    echo "<p style='color:green'>✓ Database 'communication_app' created!</p>";
    
    $pdo->exec("USE communication_app");
    
    // Create users table
    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);
    echo "<p style='color:green'>✓ Users table created!</p>";
    
    // Create messages table
    $sql = "CREATE TABLE messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql);
    echo "<p style='color:green'>✓ Messages table created!</p>";
    
    // Create demo users with bcrypt
    $hashed_password = password_hash('password123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, full_name) VALUES (?, ?, ?)");
    $stmt->execute(['person1', $hashed_password, 'Person One']);
    $stmt->execute(['person2', $hashed_password, 'Person Two']);
    
    echo "<p style='color:green'>✓ Demo users created!</p>";
    
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin-top: 20px;'>";
    echo "<h2 style='color:green'>✓ Setup Complete!</h2>";
    echo "<p><strong>Demo Users:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <strong>person1</strong> | Password: <strong>password123</strong></li>";
    echo "<li>Username: <strong>person2</strong> | Password: <strong>password123</strong></li>";
    echo "</ul>";
    echo "<p><a href='index.php' style='display: inline-block; margin-top: 10px; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Go to Login →</a></p>";
    echo "</div>";
    
} catch(PDOException $e) {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; color: red;'>";
    echo "<h2 style='color:red'>Error:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Make sure:</p>";
    echo "<ul>";
    echo "<li>XAMPP is running (Apache and MySQL services are started)</li>";
    echo "<li>MySQL username is 'root' with no password</li>";
    echo "<li>MySQL is running on port 3306</li>";
    echo "</ul>";
    echo "</div>";
}
?>