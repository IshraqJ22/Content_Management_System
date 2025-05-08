<?php
require 'db_config.php';

session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $name = $_POST['name'];
    $phone_no = $_POST['phone_no'];
    $bio = $_POST['bio'];
    $profile_picture = $_FILES['profile_picture']['name'];
    $date_of_birth = DateTime::createFromFormat('d-m-Y', $_POST['date_of_birth'])->format('Y-m-d');
    $isAdmin = ($_POST['admin_password'] === 'Xribmssjrx22@22') ? 1 : 0;

    $stmt = $pdo->query("SELECT MAX(user_id) AS max_id FROM users");
    $newUserId = str_pad((int)$stmt->fetch()['max_id'] + 1, 4, '0', STR_PAD_LEFT);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        echo "<p style='color:red;'>Username already exists.</p>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (user_id, username, password_hash, email, name, phone_no, date_of_birth, bio, profile_picture, is_online, is_admin) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)");
        $stmt->execute([
            $newUserId,
            $username,
            password_hash($password, PASSWORD_BCRYPT),
            $email,
            $name,
            $phone_no,
            $date_of_birth,
            $bio,
            $profile_picture,
            $isAdmin
        ]);

        if (!empty($_FILES['profile_picture']['tmp_name'])) {
            $uploadDir = "uploads/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadDir . $profile_picture);
        }

        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <link rel="icon" href="images/icon.ico" type="image/x-icon">
    <link rel="stylesheet" href="bootstrap.css">
    <style>
        body {
            background-color: #000000;
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-container img {
            width: 100px;
            height: auto;
            margin: 20px 0;
        }

        .registration-container {
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        .registration-container h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-group label {
            width: 120px;
            text-align: right;
            margin-right: 10px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea {
            flex: 1;
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .form-group input[type="file"] {
            padding: 5px;
        }

        .form-group textarea {
            resize: none;
        }

        .btn-primary {
            background-color: #f3e8ff;
            color: #6c63ff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .btn-primary:hover {
            background-color: #e0d8ff;
        }
    </style>
</head>

<body>
    <div class="logo-container">
        <img src="images/BLOGr_logo.png" alt="BLOGr Logo">
    </div>
    <div class="registration-container">
        <h1>Register new account</h1>
        <form action="registration.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" placeholder="Full name" required>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of birth</label>
                <input type="text" name="date_of_birth" id="date_of_birth" placeholder="DD-MM-YYYY" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="email@domain.com" required>
            </div>
            <div class="form-group">
                <label for="phone_no">Phone Number</label>
                <input type="text" id="phone_no" name="phone_no" placeholder="Phone number" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="********" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Unique Username" required>
            </div>
            <div class="form-group">
                <label for="profile_picture">Profile picture</label>
                <input type="file" id="profile_picture" name="profile_picture">
            </div>
            <div class="form-group">
                <label for="bio">Enter Bio</label>
                <textarea id="bio" name="bio" rows="4" placeholder="Say something about yourself.."></textarea>
            </div>
            <div class="form-group">
                <label for="admin_password">Admin password</label>
                <input type="password" id="admin_password" name="admin_password" placeholder="********">
            </div>
            <button type="submit" class="btn-primary">✔️ Register</button>
        </form>
    </div>
</body>

</html>
<?php
$pdo = null;
?>