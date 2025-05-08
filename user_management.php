<?php
require 'db_config.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT is_admin FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

if (!$user || $user['is_admin'] != 1) {
    echo "<p style='color:red;'>Access denied. Admin privileges are required to access this page.</p>";
    exit;
}

// Fetch all users from the database
$stmt = $pdo->query("SELECT user_id, username, email, name, phone_no, date_of_birth, bio, is_admin FROM users ORDER BY user_id ASC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all pending blog posts
$stmt = $pdo->query("SELECT blogs.*, users.username AS author_username FROM blogs JOIN users ON blogs.user_id = users.user_id WHERE blogs.status = 'pending' ORDER BY blogs.created_at DESC");
$pendingPosts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all pending comments
$stmt = $pdo->query("
    SELECT comments.*, users.username AS commenter_username 
    FROM comments 
    JOIN users ON comments.user_id = users.user_id 
    WHERE comments.status = 'Pending' 
    ORDER BY comments.created_at DESC
");
$pendingComments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle admin actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_user'])) {
        $userId = $_POST['user_id'];

        // Prevent admins from deleting themselves
        $stmt = $pdo->prepare("SELECT username FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $userToDelete = $stmt->fetch();

        if ($userToDelete && $userToDelete['username'] === $_SESSION['username']) {
            echo "<script>alert('You cannot delete your own account.');</script>";
        } else {
            try {
                // Begin a transaction
                $pdo->beginTransaction();

                // Fetch all blog IDs associated with the user
                $stmt = $pdo->prepare("SELECT blog_id FROM blogs WHERE user_id = ?");
                $stmt->execute([$userId]);
                $blogIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

                if (!empty($blogIds)) {
                    // Delete likes associated with the user's blogs
                    $stmt = $pdo->prepare("DELETE FROM likes WHERE blog_id IN (" . implode(',', array_fill(0, count($blogIds), '?')) . ")");
                    $stmt->execute($blogIds);

                    // Delete comments associated with the user's blogs
                    $stmt = $pdo->prepare("DELETE FROM comments WHERE blog_id IN (" . implode(',', array_fill(0, count($blogIds), '?')) . ")");
                    $stmt->execute($blogIds);

                    // Delete the user's blogs
                    $stmt = $pdo->prepare("DELETE FROM blogs WHERE blog_id IN (" . implode(',', array_fill(0, count($blogIds), '?')) . ")");
                    $stmt->execute($blogIds);
                }

                // Delete likes made by the user
                $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ?");
                $stmt->execute([$userId]);

                // Delete comments made by the user
                $stmt = $pdo->prepare("DELETE FROM comments WHERE user_id = ?");
                $stmt->execute([$userId]);

                // Delete follows associated with the user
                $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? OR followed_id = ?");
                $stmt->execute([$userId, $userId]);

                // Delete notifications associated with the user
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
                $stmt->execute([$userId]);

                // Finally, delete the user
                $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
                $stmt->execute([$userId]);

                // Commit the transaction
                $pdo->commit();

                echo "<script>alert('User deleted successfully.'); window.location.href = 'user_management.php';</script>";
            } catch (Exception $e) {
                // Rollback the transaction in case of an error
                $pdo->rollBack();
                echo "<p style='color:red;'>Failed to delete the user. Error: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }

    if (isset($_POST['approve_post'])) {
        $blogId = $_POST['blog_id'];
        $adminUsername = $_SESSION['username'];

        // Fetch the user_id of the admin
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$adminUsername]);
        $admin = $stmt->fetch();

        if ($admin) {
            $adminId = $admin['user_id'];

            // Update the blog post to mark it as approved and set the approved_by field
            $stmt = $pdo->prepare("UPDATE blogs SET status = 'approved', approved_by = ? WHERE blog_id = ?");
            $stmt->execute([$adminId, $blogId]);

            echo "<script>alert('Blog post approved successfully.'); window.location.href = 'user_management.php';</script>";
        } else {
            echo "<script>alert('Failed to approve the post. Admin not found.');</script>";
        }
    }

    if (isset($_POST['delete_post'])) {
        $blogId = $_POST['blog_id'];
        $stmt = $pdo->prepare("DELETE FROM blogs WHERE blog_id = ?");
        $stmt->execute([$blogId]);
        echo "<script>alert('Blog post deleted successfully.'); window.location.href = 'user_management.php';</script>";
    }

    // Delete a comment
    if (isset($_POST['delete_comment'])) {
        $commentId = $_POST['comment_id'];
        $stmt = $pdo->prepare("DELETE FROM comments WHERE comment_id = ?");
        $stmt->execute([$commentId]);

        echo "<script>alert('Comment deleted successfully.'); window.location.href = 'user_management.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="icon" href="images/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="bootstrap.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #ffffff;
            display: flex;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            color: #ffffff; /* Ensure text is white */
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-y: auto;
            /* Enable vertical scrolling */
            padding: 0;
            /* Ensure no padding inside the sidebar */
        }

        .logo-container {
            text-align: center;
            margin-top: 0px;
            margin-bottom: 30px;
            width: 150%;
        }

        .logo-container img {
            width: 600px;
            height: auto;
            border-radius: 0;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .sidebar .user-info {
            text-align: center;
            margin-bottom: 30px;
            margin-top: 20px;
            /* Add spacing below the logo */
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
            /* Add spacing between the icon and text */
            width: 20px;
            height: 20px;
            margin-bottom: 7px;
            border-radius: 0px;
            ;
        }

        .sidebar .menu .user-management-icon {
            width: 25px;
            /* Adjusted size for the user management icon */
            height: 25px;
            margin-bottom: 0;
            /* Ensure proper alignment */
            border-radius: 0;
            /* No border radius for this specific icon */
        }

        .sidebar .menu a.user-management {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            text-decoration: none;
            color: #ffffff;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .sidebar .menu a.user-management:hover {
            background-color: #333333;
        }

        .sidebar .menu a.user-management.active {
            background-color: #000000;
            font-weight: bold;
        }

        .search-container {
            display: none;
            margin-top: 10px;
            text-align: center;
        }

        .search-container input {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            width: 80%;
            margin-right: 10px;
        }

        .search-container button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #000000;
            color: #ffffff;
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #333333;
        }

        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: #000000;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table th,
        table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #000000;
        }

        .btn-danger {
            background-color: #ff4d4d;
            color: #ffffff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-danger:hover {
            background-color: #e60000;
        }

        .btn-success {
            background-color: #28a745;
            color: #ffffff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .main-content {
            margin-left: 250px;
            /* Ensure the main content is spaced from the sidebar */
            padding: 20px;
            flex: 1;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                /* Remove margin for mobile view */
                padding: 10px;
            }
        }

        body {
            background-color: #000000; /* Changed to black */
            color: #ffffff; /* Changed to white */
        }
        .sidebar {
            background-color: #000000; /* Changed to black */
            color: #ffffff; /* Changed to white */
        }
        .container {
            background-color: #000000; /* Changed to black */
            color: #ffffff; /* Changed to white */
        }
        .btn-danger, .btn-success {
            background-color: #000000; /* Changed to black */
            color: #ffffff; /* Changed to white */
        }
        .btn-danger:hover, .btn-success:hover {
            background-color: #333333; /* Adjusted hover color */
        }
    </style>
    <script>
        function toggleSearch() {
            const searchContainer = document.getElementById('search-container');
            searchContainer.style.display = searchContainer.style.display === 'none' ? 'block' : 'none';
        }

        function searchUser() {
            const username = document.getElementById('search-input').value.trim();
            if (username) {
                window.location.href = `user_profile.php?username=${encodeURIComponent(username)}`;
            } else {
                alert('Please enter a username to search.');
            }
        }
    </script>
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
            <div class="search-container" id="search-container">
                <input type="text" id="search-input" placeholder="Search for a username...">
                <button onclick="searchUser()">Search</button>
            </div>
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
            <?php if ($user['is_admin'] == 1): ?>
                <a href="user_management.php" class="user-management">
                    <div class="icon">
                        <img src="images/admin.png" alt="Admin Icon" class="user-management-icon">
                    </div>
                    User Management
                </a>
            <?php endif; ?>
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
        <h1>User Management</h1>
        <div class="search-container" id="search-container" style="margin-bottom: 20px;">
            <input type="text" id="search-input" placeholder="Search for a username..." style="padding: 10px; font-size: 16px; border: 1px solid #ced4da; border-radius: 5px; width: 80%; margin-right: 10px;">
            <button onclick="searchUser()" style="padding: 10px 20px; font-size: 16px; background-color: #ffffff; color: #000000; border: 1px solid #E0E0E0; border-radius: 5px; cursor: pointer;">Search</button>
        </div>
        <h2>All Users</h2>
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Name</th>
                    <th>Phone Number</th>
                    <th>Date of Birth</th>
                    <th>Bio</th>
                    <th>Admin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone_no']); ?></td>
                        <td><?php echo htmlspecialchars($user['date_of_birth']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($user['bio'])); ?></td>
                        <td><?php echo $user['is_admin'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <form action="user_management.php" method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                <button type="submit" name="delete_user" class="btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Pending Blog Posts</h2>
        <table>
            <thead>
                <tr>
                    <th>Blog ID</th>
                    <th>Title</th>
                    <th>Content</th>
                    <th>Author</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingPosts as $post): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($post['blog_id']); ?></td>
                        <td><?php echo htmlspecialchars($post['title']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($post['content'])); ?></td>
                        <td><?php echo htmlspecialchars($post['author_username']); ?></td>
                        <td><?php echo htmlspecialchars($post['created_at']); ?></td>
                        <td>
                            <form action="user_management.php" method="POST" style="display: inline;">
                                <input type="hidden" name="blog_id" value="<?php echo $post['blog_id']; ?>">
                                <button type="submit" name="approve_post" class="btn-success">Approve</button>
                                <button type="submit" name="delete_post" class="btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Pending Comments</h2>
        <table>
            <thead>
                <tr>
                    <th>Comment ID</th>
                    <th>Content</th>
                    <th>Commenter</th>
                    <th>Blog ID</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingComments as $comment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($comment['comment_id']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($comment['content'])); ?></td>
                        <td><?php echo htmlspecialchars($comment['commenter_username']); ?></td>
                        <td><?php echo htmlspecialchars($comment['blog_id']); ?></td>
                        <td><?php echo htmlspecialchars($comment['created_at']); ?></td>
                        <td>
                            <form action="user_management.php" method="POST" style="display: inline;">
                                <input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>">
                                <button type="submit" name="delete_comment" class="btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>

</html>