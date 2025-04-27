<?php
require 'db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? null;
    $password = $_POST['password'] ?? null;

    if ($email && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['user_id'];

            $stmt = $pdo->prepare("UPDATE users SET is_online = 1 WHERE username = ?");
            $stmt->execute([$user['username']]);

            header("Location: landing_page.php");
            exit;
        } else {
            $errorMessage = "Invalid Email or Password.";
        }
    } else {
        $errorMessage = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="bootstrap.css">
</head>
<body class="login-page">
    <div class="logo-container">
        <img src="images/BLOGr_logo.png" alt="BLOGr Logo">
    </div>
    <div class="login-container">
        <h1>BLOGr</h1>
        <h2>Log in to your account</h2>
        <p>Enter your email to Login for this app</p>
        <?php if (isset($errorMessage)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input type="email" name="email" class="form-control" placeholder="email@domain.com" required>
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <button type="submit" class="btn-primary">
                <span>✏️</span> Login
            </button>
        </form>
        <div class="divider">
            <hr>
            <span>Or</span>
            <hr>
        </div>
        <button class="btn-secondary" onclick="window.location.href='registration.php'">
            <span>✔️</span> Register
        </button>
    </div>
</body>
</html>