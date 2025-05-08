<?php
require 'db_config.php';
session_start();

// Check if the user is an admin
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo "<p style='color:red;'>Access denied. Admin privileges are required to delete posts.</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['blog_id'])) {
    $blogId = $_POST['blog_id'];

    try {
        // Begin a transaction
        $pdo->beginTransaction();

        // Delete all likes associated with the blog post
        $stmt = $pdo->prepare("DELETE FROM likes WHERE blog_id = ?");
        $stmt->execute([$blogId]);

        // Delete all comments associated with the blog post
        $stmt = $pdo->prepare("DELETE FROM comments WHERE blog_id = ?");
        $stmt->execute([$blogId]);

        // Delete the blog post
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE blog_id = ?");
        $stmt->execute([$blogId]);

        // Commit the transaction
        $pdo->commit();

        echo "<script>alert('Blog post and associated likes and comments deleted successfully.'); window.location.href = 'landing_page.php';</script>";
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $pdo->rollBack();
        echo "<p style='color:red;'>Failed to delete the blog post. Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Post</title>
    <link rel="icon" href="images/icon.ico" type="image/x-icon">
</head>

<body>

</html>