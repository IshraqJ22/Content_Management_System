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
    echo "<p style='color:red;'>Access denied. Admin privileges are required to delete comments.</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comment_id'])) {
    $commentId = $_POST['comment_id'];

    // Delete the comment from the database
    $stmt = $pdo->prepare("DELETE FROM comments WHERE comment_id = ?");
    $stmt->execute([$commentId]);

    echo "<script>alert('Comment deleted successfully.'); window.location.href = 'landing_page.php';</script>";
}
?>
