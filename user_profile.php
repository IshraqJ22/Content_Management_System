<?php
require 'db_config.php';
session_start();

$username = isset($_GET['username']) ? $_GET['username'] : $_SESSION['username'];

// Fetch user details from the database
$stmt = $pdo->prepare("SELECT username, email, name, phone_no, date_of_birth, bio, profile_picture FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p style='color:red;'>User not found.</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['logout'])) {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }

    if (isset($_POST['remove_user']) && $username === $_SESSION['username']) {
        // Delete user data from the database
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = ?");
        $stmt->execute([$username]);

        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

// Fetch all posts created by the current user
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE user_id = (SELECT user_id FROM users WHERE username = ?) ORDER BY created_at DESC");
$stmt->execute([$username]);
$userPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="bootstrap.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

        .sidebar .user-info p {
            margin: 5px 0;
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
            height: 29px;
        }

        .btn-primary {
            padding: 5px 10px;
            font-size: 16px;
            background-color: #ffffff;
            color:rgb(0, 0, 0);
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background-color: #e0e0e0;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex: 1;
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

        .profile-header .profile-info {
            display: flex;
            flex-direction: column;
        }

        .profile-header .profile-info h1 {
            font-size: 24px;
            margin: 0;
        }

        .profile-header .profile-info p {
            margin: 5px 0;
            color: #666;
        }

        .bio-section {
            margin-bottom: 30px;
        }

        .bio-section h2 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .bio-section p {
            font-size: 16px;
            color: #333;
        }

        .posts-section {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .post-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            width: calc(50% - 10px);
            padding: 20px;
        }

        .post-card img {
            width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .post-card h3 {
            font-size: 18px;
            margin: 0 0 10px;
        }

        .post-card p {
            font-size: 14px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo-container">
            <img src="images/BLOGr_logo.png" alt="BLOGr Logo">
        </div>

        <div class="menu">
            <a href="landing_page.php" class="active">
                <div class="icon">
                    <img src="images/home.png" alt="Home Icon">
                </div>
                Home
            </a>
            <a href="#" onclick="toggleSearch()">
                <div class="icon">
                    <img src="images/search.png" alt="Search Icon">
                </div>
                Search
            </a>
            <a href="user_profile.php">
                <div class="icon">
                    <img src="images/user.png" alt="Profile Icon">
                </div>
                Profile
            </a>
            <a href="#">
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

    <div class="main-content">
        <div class="profile-header">
            <img src="<?php echo !empty($user['profile_picture']) && file_exists('uploads/' . $user['profile_picture']) ? 'uploads/' . htmlspecialchars($user['profile_picture']) : 'images/default_user.png'; ?>" alt="Profile Picture">
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($user['name']); ?></h1>
                <p>@<?php echo htmlspecialchars($user['username']); ?></p>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
        </div>

        <div class="bio-section">
            <h2>Bio</h2>
            <p><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
        </div>
        <hr>
        <div class="posts-section">
            <?php if (!empty($userPosts)): ?>
                <?php foreach ($userPosts as $post): ?>
                    <div class="post-card">
                        <img src="<?php echo !empty($post['image_url']) ? 'uploads/' . htmlspecialchars($post['image_url']) : 'images/default_banner.png'; ?>" alt="Post Image">
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        <p style="font-size: 12px; color: #666;">Created on: <?php echo htmlspecialchars($post['created_at']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No posts to display.</p>
            <?php endif; ?>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            let page = 1; // Start with the first page
            let isLoading = false;

            function loadPosts() {
                if (isLoading) return;
                isLoading = true;
                $("#loading-indicator").show();

                $.ajax({
                    url: "load_posts.php",
                    type: "GET",
                    data: {
                        page: page
                    },
                    success: function(data) {
                        if (data.trim() !== "") {
                            $("#blog-posts-container").append(data);
                            page++;
                        } else {
                            // No more posts to load
                            $("#loading-indicator").text("No more posts to load.");
                        }
                        isLoading = false;
                        $("#loading-indicator").hide();
                    },
                    error: function() {
                        console.error("Failed to load posts.");
                        isLoading = false;
                        $("#loading-indicator").hide();
                    },
                });
            }

            // Load initial posts
            loadPosts();

            // Infinite scrolling
            $(window).on("scroll", function() {
                if (
                    $(window).scrollTop() + $(window).height() >=
                    $(document).height() - 100
                ) {
                    loadPosts();
                }
            });
        });
    </script>
</body>

</html>