<?php
require 'db_config.php';

// Use a unique session identifier for each window
if (isset($_GET['session_id'])) {
    session_id($_GET['session_id']);
} elseif (isset($_COOKIE['window_session_id'])) {
    session_id($_COOKIE['window_session_id']);
} else {
    $newSessionId = bin2hex(random_bytes(16));
    setcookie('window_session_id', $newSessionId, 0, '/');
    session_id($newSessionId);
}
session_start();

// Handle logout logic before any output
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    // Set the user's is_online status to 0
    $stmt = $pdo->prepare("UPDATE users SET is_online = 0 WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);

    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Fetch user details
$stmt = $pdo->prepare("SELECT username, user_id, profile_picture, is_admin FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

// Fetch total online users
$totalOnlineUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_online = 1")->fetchColumn();

// Fetch unread notifications count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$stmt->execute([$user['user_id']]);
$unreadNotifications = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLOGr - Home</title>
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
            /* Enable vertical scrolling */
            padding: 0;
            /* Ensure no padding inside the sidebar */
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
            /* Ensure icons and text are vertically aligned */
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
            color: #333;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .sidebar .menu a.user-management:hover {
            background-color: #f0f0f0;
        }

        .sidebar .menu a.user-management.active {
            background-color: #ffffff;
            font-weight: bold;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
            flex: 1;
            /* Allow the main content to take up the remaining space */
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #ffffff;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
        }

        .header .online-users {
            font-size: 16px;
            color: #666;
        }

        .create-post {
            margin: 20px 0;
            text-align: center;
        }

        .create-post button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #ffffff;
            color: rgb(0, 0, 0);
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            cursor: pointer;
        }

        .create-post button:hover {
            background-color: #E0E0E0;
        }

        .content-banner {
            width: 100%;
            height: 200px;
            background-color: rgb(255, 253, 253);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
        }

        .blog-post {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .blog-post .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            /* Adjusted spacing */
            padding: 10px;
            /* Added padding for better spacing */
            background-color: #f9f9f9;
            /* Optional: Add a background color for clarity */
            border-bottom: 1px solid #e0e0e0;
            /* Optional: Add a separator */
        }

        .blog-post .post-header .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            /* Ensure proper spacing between elements */
        }

        .blog-post .post-header .user-info img {
            width: 40px;
            /* Adjusted size */
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .blog-post .post-header .post-meta {
            font-size: 12px;
            /* Adjusted font size */
            color: #666;
            text-align: right;
        }

        .blog-post .post-content {
            margin-bottom: 20px;
            font-size: 16px;
            color: #333;
        }

        .loading {
            text-align: center;
            margin: 20px 0;
            font-size: 16px;
            color: #666;
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
            background-color: #ffffff;
            color: rgb(0, 0, 0);
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #E0E0E0;
        }

        .btn-primary {
            padding: 5px 10px;
            font-size: 16px;
            background-color: #ffffff;
            color: rgb(0, 0, 0);
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            /* Ensure icons and text are vertically aligned */
            justify-content: center;
            gap: 10px;
        }

        .btn-primary:hover {
            background-color: #E0E0E0;
        }

        .btn-primary .icon {
            width: 20px;
            height: 20px;
            margin-bottom: 6px;
            ;
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
                window.location.href = `user_profile.php?username=${encodeURIComponent(username)}&session_id=<?php echo session_id(); ?>`;
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
        <div class="user-info" style="display: flex; align-items: center; gap: 10px; padding: 10px;">
            <img src="<?php echo !empty($user['profile_picture']) ? 'uploads/' . htmlspecialchars($user['profile_picture']) : 'images/default_user.png'; ?>" alt="Profile Picture" style="width: 130px; height: 130px; object-fit: cover;">
            <div style="display: flex; flex-direction: column;">
                <p style="margin: 0; font-weight: bold; color: black;"><?php echo htmlspecialchars($user['username']); ?></p>
                <p style="margin: 0; font-size: 14px; color: #666;">User id: <?php echo htmlspecialchars($user['user_id']); ?></p>
            </div>
        </div>
        <div class="menu">
            <a href="landing_page.php?session_id=<?php echo session_id(); ?>" class="active">
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
            <a href="user_profile.php?session_id=<?php echo session_id(); ?>">
                <div class="icon">
                    <img src="images/user.png" alt="Profile Icon">
                </div>
                Profile
            </a>
            <a href="notifications.php?session_id=<?php echo session_id(); ?>">
                <div class="icon">
                    <img src="images/notifications.png" alt="Notification Icon">
                </div>
                Notification
                <?php if ($unreadNotifications > 0): ?>
                    <span style="color: red; font-weight: bold;">(<?php echo $unreadNotifications; ?>)</span>
                <?php endif; ?>
            </a>
            <?php if ($user['is_admin'] == 1): ?>
                <a href="user_management.php?session_id=<?php echo session_id(); ?>" class="user-management">
                    <div class="icon">
                        <img src="images/admin.png" alt="Admin Icon" class="user-management-icon">
                    </div>
                    User Management
                </a>
            <?php endif; ?>
        </div>
        <form action="landing_page.php?session_id=<?php echo session_id(); ?>" method="POST" style="margin-top: auto; text-align: center; padding-bottom: 20px;">
            <button type="submit" name="logout" class="btn-primary">
                <div class="icon">
                    <img src="images/logout.png" alt="Logout Icon">
                </div>
                Logout
            </button>
        </form>
    </div>

    <div class="main-content">
        <p class="online-users">Total online users: <?php echo $totalOnlineUsers; ?></p>
        <hr>

        <div class="search-container" id="search-container">
            <input type="text" id="search-input" placeholder="Search for a username...">
            <button onclick="searchUser()">Search</button>
        </div>

        <div class="create-post">
            <a href="create_post.php?session_id=<?php echo session_id(); ?>">
                <button>Create Post</button>
            </a>
        </div>

        <div class="content">
            <div id="blog-posts-container">
                <!-- Blog posts will be dynamically loaded here -->
            </div>
            <div class="loading" id="loading-indicator" style="display: none;">Loading...</div>
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
                        page: page,
                        session_id: '<?php echo session_id(); ?>'
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