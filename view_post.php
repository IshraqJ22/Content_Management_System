<?php
require 'db_config.php';
session_start();

if (!isset($_GET['blog_id']) || !is_numeric($_GET['blog_id'])) {
    echo "<p style='color:red;'>Invalid blog post.</p>";
    exit;
}

$blogId = (int)$_GET['blog_id'];


// Fetch the blog post details
$stmt = $pdo->prepare("
    SELECT blogs.*, 
           users.username AS author_username, 
           users.profile_picture, 
           (SELECT COUNT(*) FROM likes WHERE likes.blog_id = blogs.blog_id) AS like_count
    FROM blogs 
    JOIN users ON blogs.user_id = users.user_id 
    WHERE blogs.blog_id = ?
");
$stmt->execute([$blogId]);
$post = $stmt->fetch();

if (!$post) {
    echo "<p style='color:red;'>Blog post not found.</p>";
    exit;
}

// Fetch all comments for the blog post
$commentStmt = $pdo->prepare("
    SELECT comments.content, comments.created_at, users.username AS commenter_username 
    FROM comments 
    JOIN users ON comments.user_id = users.user_id 
    WHERE comments.blog_id = ? AND comments.status = 'Approved' 
    ORDER BY comments.created_at DESC
");
$commentStmt->execute([$blogId]);
$comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch the logged-in user's user_id
$stmt = $pdo->prepare("SELECT user_id, username, profile_picture FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p style='color:red;'>User not found.</p>";
    exit;
}
 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Post</title>
    <link rel="stylesheet" href="bootstrap.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            display: flex;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #ffffff;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-y: auto;
            padding: 0;
        }

        .logo-container {
            text-align: center;
            margin-top: 0px;
            margin-bottom: 30px;
            width: 100%;
        }

        .logo-container img {
            width: 400px;
            height: auto;
            border-radius: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .sidebar .user-info {
            text-align: center;
            margin-bottom: 30px;
            margin-top: 20px;
        }

        .sidebar .user-info img {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
        }

        .sidebar .menu {
            width: 100%;
        }

        .sidebar .menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: #333;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .sidebar .menu a:hover {
            background-color: #f0f0f0;
        }

        .sidebar .menu a.active {
            background-color: #ffffff;
            font-weight: bold;
        }

        .sidebar .menu .icon {
            margin-right: 10px;
            width: 20px;
            height: 20px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex: 1;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        ul li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        ul li:last-child {
            border-bottom: none;
        }

        ul li span {
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="sidebar">
        <div class="logo-container">
            <img src="images/BLOGr_logo.png" alt="BLOGr Logo">
        </div>
        <div class="user-info">
            <img src="<?php echo !empty($user['profile_picture']) ? 'uploads/' . htmlspecialchars($user['profile_picture']) : 'images/default_user.png'; ?>" alt="Profile Picture">
            <p style="margin: 0; font-weight: bold; color: black;"><?php echo htmlspecialchars($user['username']); ?></p>
        </div>
        <div class="menu">
            <a href="landing_page.php">
                <div class="icon">
                    <img src="images/home.png" alt="Home Icon">
                </div>
                Home
            </a>
            <a href="user_profile.php">
                <div class="icon">
                    <img src="images/user.png" alt="Profile Icon">
                </div>
                Profile
            </a>
            <a href="notifications.php" class="active">
                <div class="icon">
                    <img src="images/notifications.png" alt="Notification Icon">
                </div>
                Notification
            </a>
        </div>
        <form action="landing_page.php" method="POST" style="margin-top: auto; text-align: center; padding-bottom: 20px;">
            <button type="submit" name="logout" class="btn-primary">
                <div class="icon">
                    <img src="images/logout.png" alt="Logout Icon">
                </div>
                Logout
            </button>
        </form>
    </div>
    <div class="container">
        <h1><?php echo htmlspecialchars($post['title']); ?></h1>
        <p style="font-size: 14px; color: #666;">By: <?php echo htmlspecialchars($post['author_username']); ?> | Created on: <?php echo htmlspecialchars($post['created_at']); ?></p>
        <img src="<?php echo !empty($post['image_url']) ? 'uploads/' . htmlspecialchars($post['image_url']) : 'images/default_banner.png'; ?>" alt="Post Image" style="width: 100%; height: auto; margin-bottom: 20px;">
        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
        <p style="font-size: 14px; color: #666;">Likes: <?php echo htmlspecialchars($post['like_count']); ?></p>
        <hr>
        <h2>Comments</h2>
        <?php foreach ($comments as $comment): ?>
            <p>
                <strong>
                    <a href="user_profile.php?username=<?php echo urlencode($comment['commenter_username']); ?>" style="text-decoration: none; color: #6c63ff;">
                        <?php echo htmlspecialchars($comment['commenter_username']); ?>
                    </a>:
                </strong> 
                <?php echo htmlspecialchars($comment['content']); ?> 
                <span style="font-size: 12px; color: #666;">(<?php echo htmlspecialchars($comment['created_at']); ?>)</span>
            </p>
        <?php endforeach; ?>
        <hr>
        <form action="comment_post.php" method="POST">
            <input type="hidden" name="blog_id" value="<?php echo $blogId; ?>">
            <textarea name="comment" rows="3" placeholder="Write a comment..." style="width: 100%; padding: 10px; margin-bottom: 10px;" required></textarea>
            <button type="submit" style="padding: 10px 20px; font-size: 14px; background-color: #ffffff; color:rgb(0, 0, 0); border: 1px solid #E0E0E0; border-radius: 5px; cursor: pointer;">Post Comment</button>
        </form>
    </div>
</body>
</html>
