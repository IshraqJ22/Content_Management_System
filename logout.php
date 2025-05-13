<?php
require 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    // Set the user's is_online status to 0
    $stmt = $pdo->prepare("UPDATE users SET is_online = 0 WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);

    session_unset();
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="icon" href="images/icon.ico" type="image/x-icon">
</head>

<body>
    <p>You have been logged out successfully.</p>
    <a href="login.php">Login again</a>
</body>

</html>