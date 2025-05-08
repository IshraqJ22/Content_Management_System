<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Fetch the logged-in user's user_id
$stmt = $pdo->prepare("SELECT user_id, username, profile_picture FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p style='color:red;'>User not found.</p>";
    exit;
}

$userId = $user['user_id'];

// Fetch notifications for the user
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark all notifications as read
$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
$stmt->execute([$userId]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="icon" href="images/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="bootstrap.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #000000;
            display: flex;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #000000;
            display: flex;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #000000;
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
            color: #ffffff;
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
            background-color: #000000;
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
        <div class="container">
            <h1>Notifications</h1>
            <?php if (!empty($notifications)): ?>
                <ul>
                    <?php foreach ($notifications as $notification): ?>
                        <li>
                            <?php echo htmlspecialchars($notification['message']); ?>
                            <span>(<?php echo htmlspecialchars($notification['created_at']); ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No notifications to display.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>