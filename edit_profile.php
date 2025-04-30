<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Fetch user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user) {
    echo "<p style='color:red;'>User not found.</p>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone_no'];
    $bio = $_POST['bio'];
    $date_of_birth = DateTime::createFromFormat('Y-m-d', $_POST['date_of_birth'])->format('Y-m-d');
    $newUsername = $_POST['username'];

    // Check if the new username is already taken
    if ($newUsername !== $username) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$newUsername]);
        if ($stmt->fetchColumn() > 0) {
            echo "<p style='color:red;'>Username is already taken. Please choose a different one.</p>";
            exit;
        }
    }

    // Handle profile picture upload
    $profilePicture = $user['profile_picture'];
    if (!empty($_FILES['profile_picture']['tmp_name'])) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $profilePicture = basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $uploadDir . $profilePicture);
    }

    // Update user details
    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, phone_no = ?, bio = ?, date_of_birth = ?, profile_picture = ?, username = ? WHERE username = ?");
    $stmt->execute([$name, $email, $phone_no, $bio, $date_of_birth, $profilePicture, $newUsername, $username]);

    // Update session username
    $_SESSION['username'] = $newUsername;

    header("Location: user_profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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
            padding: 10px 20px;
            font-size: 16px;
            background-color: #ffffff;
            color:rgb(0, 0, 0);
            border: 1px solid #E0E0E0;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #E0E0E0;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Edit Profile</h1>
        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone_no">Phone Number</label>
                <input type="text" id="phone_no" name="phone_no" value="<?php echo htmlspecialchars($user['phone_no']); ?>" required>
            </div>
            <div class="form-group">
                <label for="date_of_birth">Date of Birth</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth']); ?>" required>
            </div>
            <div class="form-group">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="profile_picture">Profile Picture</label>
                <input type="file" id="profile_picture" name="profile_picture">
            </div>
            <button type="submit" class="btn-primary">Save Changes</button>
        </form>
    </div>
</body>

</html>
