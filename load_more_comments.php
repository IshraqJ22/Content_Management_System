<?php
require 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['blog_id'])) {
    $blogId = $_POST['blog_id'];

    // Fetch all comments for the blog post
    $stmt = $pdo->prepare("
        SELECT comments.content, comments.created_at, users.username 
        FROM comments 
        JOIN users ON comments.user_id = users.user_id 
        WHERE comments.blog_id = ? 
        ORDER BY comments.created_at DESC
    ");
    $stmt->execute([$blogId]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($comments as $comment) {
        echo "<p><strong>" . htmlspecialchars($comment['username']) . ":</strong> " . htmlspecialchars($comment['content']) . "</p>";
    }

    echo '<a href="landing_page.php" style="color: blue; text-decoration: none;">Back to Posts</a>';
}
