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
    SELECT comments.comment_id, comments.content, comments.created_at, users.username AS commenter_username 
    FROM comments 
    JOIN users ON comments.user_id = users.user_id 
    WHERE comments.blog_id = ? 
    ORDER BY comments.created_at DESC
");
$commentStmt->execute([$blogId]);
$comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
// Fetch the logged-in user's user_id
$stmt = $pdo->prepare("SELECT user_id, username, profile_picture, is_admin FROM users WHERE username = ?");
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
    <link rel="icon" href="images/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="bootstrap.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #000000;
            /* Changed to black */
            color: #ffffff;
            /* Changed to white */
            display: flex;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #000000;
            /* Changed to black */
            color: #ffffff;
            /* Changed to white */
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
            width: 100px;
            height: 100px;
            border-radius: 0;
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
            color: #ffffff;
            /* Changed to white */
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .sidebar .menu a:hover {
            background-color: #333333;
        }

        .sidebar .menu a.active {
            background-color: #000000;
            font-weight: bold;
        }

        .sidebar .menu .icon {
            margin-right: 10px;
            width: 20px;
            height: 29px;
        }

        .btn-primary {
            padding: 5px 10px;
            font-size: 16px;
            background-color: #000000;
            /* Changed to black */
            color: #ffffff;
            /* Changed to white */
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background-color: #333333;
            /* Adjusted hover color */
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex: 1;
            min-height: 100vh;
            background-color: #000000;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
        }

        .profile-header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #000000;
            /* Changed to black */
            color: #ffffff;
            /* Changed to white */
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        img {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
        }

        .comment-section {
            margin-top: 30px;
        }

        .comment-section h2 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .comment-section p {
            margin-bottom: 10px;
        }

        .comment-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ced4da;
            border-radius: 5px;
        }

        .comment-form button {
            padding: 10px 20px;
            font-size: 14px;
            background-color: #000000;
            /* Changed to black */
            color: #ffffff;
            /* Changed to white */
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            cursor: pointer;
        }

        .comment-form button:hover {
            background-color: #E0E0E0;
        }


        .delete-button {
            padding: 10px 20px;
            font-size: 14px;
            background-color: #ff4d4d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .delete-button:hover {
            background-color: #e60000;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
                padding: 10px;
            }

            .container {
                padding: 10px;
            }
        }

        body {
            background-color: #000000;
            /* Ensure background is black */
            color: #ffffff;
            /* Ensure text is white */
        }

        .sidebar {
            background-color: #000000;
            /* Ensure sidebar is black */
            color: #ffffff;
            /* Ensure text is white */
        }

        .container {
            background-color: #000000;
            /* Ensure container is black */
            color: #ffffff;
            /* Ensure text is white */
            border: 1px solid #333333;
            /* Adjust border color */
        }

        .btn-primary {
            background-color: #000000;
            /* Ensure buttons are black */
            color: #ffffff;
            /* Ensure text is white */
        }

        .btn-primary:hover {
            background-color: #333333;
            /* Adjust hover color */
        }

        input,
        textarea {
            background-color: #000000;
            /* Ensure inputs are black */
            color: #ffffff;
            /* Ensure text is white */
            border: 1px solid #ffffff;
            /* Ensure border is white */
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
            <p style="color: #ffffff; font-weight: bold;"><?php echo htmlspecialchars($user['username']); ?></p>
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
                Notifications
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
    <div class="main-content">
        <div class="container">
            <h1><?php echo htmlspecialchars($post['title']); ?></h1>
            <p>By: <?php echo htmlspecialchars($post['author_username']); ?> | Created on: <?php echo htmlspecialchars($post['created_at']); ?></p>
            <img src="<?php echo !empty($post['image_url']) ? 'uploads/' . htmlspecialchars($post['image_url']) : 'images/default_banner.png'; ?>" alt="Post Image">
            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            <p>Likes: <?php echo htmlspecialchars($post['like_count']); ?></p>
            <form action="like_post.php" method="POST" style="margin-bottom: 20px;">
                <input type="hidden" name="blog_id" value="<?php echo $blogId; ?>">
                <button type="submit" class="btn-primary">
                    <?php
                    // Check if the user has already liked the post
                    $stmt = $pdo->prepare("SELECT * FROM likes WHERE blog_id = ? AND user_id = ?");
                    $stmt->execute([$blogId, $user['user_id']]);
                    $isLiked = $stmt->rowCount() > 0;
                    echo $isLiked ? "Unlike" : "Like";
                    ?>
                </button>
            </form>
            <hr>
            <div class="comment-section">
                <h2>Comments</h2>
                <div style="margin-top: 10px; border-top: 1px solid #ddd; padding-top: 10px;">
                    <h4 style="color: #ffffff;">Comments:</h4>
                    <?php foreach ($comments as $comment): ?>
                        <p>
                            <strong>
                                <a href="user_profile.php?username=<?php echo urlencode($comment['commenter_username']); ?>" style="text-decoration: none; color: #ffffff;">
                                    <?php echo htmlspecialchars($comment['commenter_username']); ?>
                                </a>:
                            </strong>
                            <span style="color: #ffffff;"><?php echo htmlspecialchars($comment['content']); ?></span>
                            <span style="font-size: 12px; color: #cccccc;">(<?php echo htmlspecialchars($comment['created_at']); ?>)</span>
                            <?php if ($user['user_id'] == $post['user_id'] || $user['username'] == $comment['commenter_username'] || $user['is_admin'] == 1): ?>
                        <form action="delete_comment.php" method="POST" style="display: inline;">
                            <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                            <button type="submit" class="delete-button">Delete</button>
                        </form>
                    <?php endif; ?>
                    </p>
                <?php endforeach; ?>
                <form action="comment_post.php" method="POST" class="comment-form">
                    <input type="hidden" name="blog_id" value="<?php echo $blogId; ?>">
                    <textarea name="comment" rows="3" placeholder="Write a comment..." required></textarea>
                    <button type="submit">Post Comment</button>
                </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>