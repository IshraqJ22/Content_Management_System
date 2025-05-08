<?php
require 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['blog_id'], $_POST['comment'])) {
    $blogId = $_POST['blog_id'];
    $comment = $_POST['comment'];

    // Fetch the logged-in user's user_id
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "<p style='color:red;'>User not found.</p>";
        exit;
    }

    $userId = $user['user_id'];

    // Insert the comment into the database with status 'Pending'
    $stmt = $pdo->prepare("INSERT INTO comments (blog_id, user_id, content, created_at, status) VALUES (?, ?, ?, NOW(), 'Pending')");
    $stmt->execute([$blogId, $userId, $comment]);

    // Fetch the post owner's user_id
    $stmt = $pdo->prepare("SELECT user_id FROM blogs WHERE blog_id = ?");
    $stmt->execute([$blogId]);
    $postOwner = $stmt->fetch();

    if ($postOwner && $postOwner['user_id'] != $userId) {
        // Insert a notification for the post owner
        $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
        $stmt->execute([$postOwner['user_id'], "A new comment on your post is awaiting approval."]);
    }

    header("Location: view_post.php?blog_id=$blogId");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comment Post</title>
    <link rel="icon" href="images/icon.ico" type="image/x-icon">
</head>
<body>
</html>
