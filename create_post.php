<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Fetch user details
$stmt = $pdo->prepare("SELECT username, user_id, profile_picture, is_admin FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author_username = $_SESSION['username'];

    // Fetch the user_id of the logged-in user
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->execute([$author_username]);
    $user = $stmt->fetch();
    if (!$user) {
        echo "<p style='color:red;'>User not found.</p>";
        exit;
    }
    $user_id = $user['user_id'];

    // Generate a unique blog_id
    $stmt = $pdo->query("SELECT MAX(blog_id) AS max_id FROM blogs");
    $result = $stmt->fetch();
    $newBlogId = (int)$result['max_id'] + 1;

    // Handle file upload for the blog image banner
    $imageBanner = null;
    if (!empty($_FILES['image_banner']['tmp_name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create the directory if it doesn't exist
        }

        $imageBanner = basename($_FILES['image_banner']['name']);
        move_uploaded_file($_FILES['image_banner']['tmp_name'], $uploadDir . $imageBanner);
    }

    // Insert the blog post into the database
    $stmt = $pdo->prepare("INSERT INTO blogs (blog_id, title, content, image_url, user_id, created_at, status) VALUES (?, ?, ?, ?, ?, NOW(), 'pending')");
    $stmt->execute([$newBlogId, $title, $content, $imageBanner, $user_id]);

    header("Location: landing_page.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link rel="icon" href="images/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="bootstrap.css">
    <style>
        body {
            background-color: #000000;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #000000; /* Changed to black */
            color: #ffffff; /* Changed to white */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-y: auto;
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
            color: #ffffff; /* Changed to white */
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
            height: 20px;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #000000;
            color: #ffffff;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ffffff;
            border-radius: 5px;
        }

        .form-group textarea {
            resize: none;
        }

        .btn-primary {
            background-color: #000000;
            border-color: #e0e0e0;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            color: #ffffff;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #333333;
            border-color: #e0e0e0;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex: 1;
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
            <a href="notifications.php">
                <div class="icon">
                    <img src="images/notifications.png" alt="Notification Icon">
                </div>
                Notifications
            </a>
            <!-- Removed the user management button -->
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
            <h1>Create a New Blog Post</h1>
            <form action="create_post.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" placeholder="Enter the title" required>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <textarea id="content" name="content" rows="6" placeholder="Write your blog content here..." required></textarea>
                </div>
                <div class="form-group">
                    <label for="image_banner">Blog Image Banner</label>
                    <input type="file" id="image_banner" name="image_banner">
                </div>
                <button type="submit" class="btn-primary">Post</button>
            </form>
        </div>
    </div>
</body>

</html>