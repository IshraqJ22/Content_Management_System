<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

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
    <link rel="stylesheet" href="bootstrap.css">
    <style>
        body {
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
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
            border: 1px solid #ced4da;
            border-radius: 5px;
        }

        .form-group textarea {
            resize: none;
        }

        .btn-primary {
            background-color: #6c63ff;
            border-color: #6c63ff;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            color: #ffffff;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #5a54d6;
            border-color: #5a54d6;
        }
    </style>
</head>

<body>
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
</body>

</html>