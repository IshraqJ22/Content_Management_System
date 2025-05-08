<?php
require 'db_config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Like Post</title>
    <link rel="icon" href="images/icon.ico" type="image/x-icon">
</head>

<body>
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['blog_id'])) {
        $blogId = $_POST['blog_id'];

        // Fetch the logged-in user's user_id
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$_SESSION['username']]);
        $user = $stmt->fetch();

        if (!$user) {
            echo "<p style='color:red;'>User not found.</p>";
            exit;
        }

        $userId = $user['user_id'];

        // Check if the user has already liked the post
        $stmt = $pdo->prepare("SELECT * FROM likes WHERE blog_id = ? AND user_id = ?");
        $stmt->execute([$blogId, $userId]);

        if ($stmt->rowCount() > 0) {
            // Remove the like
            $stmt = $pdo->prepare("DELETE FROM likes WHERE blog_id = ? AND user_id = ?");
            $stmt->execute([$blogId, $userId]);
        } else {
            // Add a like
            $stmt = $pdo->prepare("INSERT INTO likes (blog_id, user_id) VALUES (?, ?)");
            $stmt->execute([$blogId, $userId]);

            // Fetch the post owner's user_id
            $stmt = $pdo->prepare("SELECT user_id FROM blogs WHERE blog_id = ?");
            $stmt->execute([$blogId]);
            $postOwner = $stmt->fetch();

            if ($postOwner && $postOwner['user_id'] != $userId) {
                // Insert a notification for the post owner
                $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $stmt->execute([$postOwner['user_id'], "Your post received a new like."]);
            }
        }

        header("Location: landing_page.php");
        exit;
    }
    ?>
</body>

</html>