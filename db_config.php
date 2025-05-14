<?php
$host = 'localhost';
$dbname = 'cms';
$username = 'root';
$password = 'Xribmssjrx22'; // Replace with your actual password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create likes table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        blog_id INT NOT NULL,
        username VARCHAR(255) NOT NULL,
        UNIQUE(blog_id, username)
    )");

    // Create comments table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        blog_id INT NOT NULL,
        username VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // Create notifications table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id)
    )");

    // Check if the 'last_activity' column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'last_activity'");
    $columnExists = $stmt->rowCount() > 0;

    // Add the 'last_activity' column if it doesn't exist
    if (!$columnExists) {
        $pdo->exec("ALTER TABLE users ADD COLUMN last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}