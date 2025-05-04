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
    <style>
        body {
            background-color: #000000; /* Changed to black */
            color: #ffffff; /* Changed to white */
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background-color: #000000; /* Changed to black */
            color: #ffffff; /* Changed to white */
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .login-container h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .login-container p {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        .login-container input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .login-container button {
            width: 100%;
            padding: 10px;
            background-color: #000000; /* Changed to black */
            color: #ffffff; /* Changed to white */
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            font-size: 16px;
            cursor: pointer;
        }

        .login-container button:hover {
            background-color: #333333; /* Adjusted hover color */
        }

        .login-container .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
        }

        .login-container .divider hr {
            flex: 1;
            border: none;
            border-top: 1px solid #ddd;
        }

        .login-container .divider span {
            margin: 0 10px;
            font-size: 14px;
            color: #666;
        }

        .login-container .btn-secondary {
            background-color: #ffffff;
            color:rgb(0, 0, 0);
            border: 1px solid #e0e0e0;
            padding: 10px;
            font-size: 16px;
            border-radius: 20px;
            cursor: pointer;
        }

        .login-container .btn-secondary:hover {
            background-color: #f3f3f3;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>BLOGr</h1>
        <h2>Log in to your account</h2>
        <p>Enter your email to log in to the app</p>
        <?php if (isset($errorMessage)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="email@domain.com" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <div class="divider">
            <hr>
            <span>Or</span>
            <hr>
        </div>
        <button class="btn-secondary" onclick="window.location.href='registration.php'">Register</button>
    </div>
</body>
</html>