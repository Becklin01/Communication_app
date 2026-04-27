DROP DATABASE IF EXISTS communication_app;
CREATE DATABASE communication_app;
USE communication_app;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Messages table
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert two users (password: password123)
-- Using bcrypt hash for 'password123'
INSERT INTO users (username, password, full_name) VALUES 
('person1', '$2y$10$YourHashHere', 'Person One'),
('person2', '$2y$10$YourHashHere', 'Person Two');

-- Alternative: Create a simple password (use this for testing)
-- Note: This uses MD5 which is not secure, but works for testing
-- REPLACE the above INSERT with this if you have issues:
-- INSERT INTO users (username, password, full_name) VALUES 
-- ('person1', MD5('password123'), 'Person One'),
-- ('person2', MD5('password123'), 'Person Two');