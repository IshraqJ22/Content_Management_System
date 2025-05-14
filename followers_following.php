<?php
require 'db_config.php';
session_start();

if (!isset($_GET['type'], $_GET['user_id']) || !in_array($_GET['type'], ['followers', 'following'])) {
    echo "<p style='color:red;'>Invalid request.</p>";
    exit;
}

$type = $_GET['type'];
$userId = (int)$_GET['user_id'];

// Fetch the list of followers or following users
if ($type === 'followers') {
    $stmt = $pdo->prepare("
        SELECT users.username, users.name, users.profile_picture 
        FROM follows 
        JOIN users ON follows.follower_id = users.user_id 
        WHERE follows.followed_id = ?
    ");
} else { // type === 'following'
    $stmt = $pdo->prepare("
        SELECT users.username, users.name, users.profile_picture 
        FROM follows 
        JOIN users ON follows.followed_id = users.user_id 
        WHERE follows.follower_id = ?
    ");
}
$stmt->execute([$userId]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($type); ?></title>
    <link rel="stylesheet" href="bootstrap.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #000000;
            margin: 0;
            display: flex;
        }

        .user-list {
            max-width: 600px;
            margin: 20px auto;
            background-color: #000000;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
        }

        .user-list h1 {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
            
        }

        .user-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            
        }

        .user-item:last-child {
            border-bottom: none;
            
        }

        .user-item img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
            
        }

        .user-item a {
            text-decoration: none;
            color: #333;
            font-size: 16px;
            font-weight: bold;
            color: #FFFFFF;
        }

        .user-item span {
            display: block;
            font-size: 14px;
            color: #666;
            
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
            box-shadow: 0 4px 6px #000000;
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
            color: #FFFFFF;
        }

        .sidebar .menu a:hover {
            background-color: #000000;
        }

        .sidebar .menu a.active {
            background-color: #000000;
            font-weight: bold;
        }

        .sidebar .menu .icon {
            margin-right: 10px;
            width: 20px;
            height: 30px;
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
        <div class="user-list">
            <h1><?php echo ucfirst($type); ?></h1>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <div class="user-item">
                        <img src="<?php echo !empty($user['profile_picture']) && file_exists('uploads/' . $user['profile_picture']) ? 'uploads/' . htmlspecialchars($user['profile_picture']) : 'images/default_user.png'; ?>" alt="Profile Picture">
                        <div>
                            <a href="user_profile.php?username=<?php echo urlencode($user['username']); ?>">
                                <?php echo htmlspecialchars($user['name']); ?>
                            </a>
                            <span>@<?php echo htmlspecialchars($user['username']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; color: #666;">No users to display.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>