<?php
require 'db_config.php';
session_start();

// Check if the user is an admin
$isAdmin = false;
if (isset($_SESSION['username'])) {
    $stmt = $pdo->prepare("SELECT is_admin FROM users WHERE username = ?");
    $stmt->execute([$_SESSION['username']]);
    $user = $stmt->fetch();
    $isAdmin = $user && $user['is_admin'] == 1;
}

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Fetch the logged-in user's details
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
$stmt->execute([$_SESSION['username']]);
$loggedInUser = $stmt->fetch();

if (!$loggedInUser) {
    echo "<p style='color:red;'>User not found.</p>";
    exit;
}

$loggedInUserId = $loggedInUser['user_id'];

if (!isset($_GET['page']) || !is_numeric($_GET['page'])) {
    http_response_code(400);
    exit("Invalid page number.");
}

$page = (int)$_GET['page'];
$postsPerPage = 5; // Number of posts to load per page
$offset = ($page - 1) * $postsPerPage;

// Fetch blog posts and join with users table to get the author's username
$stmt = $pdo->prepare("
    SELECT blogs.*, 
           users.username AS author_username, 
           users.profile_picture, 
           (SELECT username FROM users WHERE users.user_id = blogs.approved_by) AS approved_by_username, -- Fetch admin username
           (SELECT COUNT(*) FROM likes WHERE likes.blog_id = blogs.blog_id) AS like_count,
           (SELECT COUNT(*) FROM comments WHERE comments.blog_id = blogs.blog_id) AS comment_count
    FROM blogs 
    JOIN users ON blogs.user_id = users.user_id 
    WHERE blogs.status = 'approved' -- Only fetch approved posts
    ORDER BY blogs.created_at DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $postsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($posts as $post) {
    // Use a default image if image_url is empty or NULL
    $imageUrl = !empty($post['image_url']) ? "uploads/" . htmlspecialchars($post['image_url']) : "images/default_banner.png";
    ?>
    <div class="blog-post" data-blog-id="<?php echo $post['blog_id']; ?>">
        <div class="content-banner" style="background-color: #d9d9d9; height: 200px; display: flex; justify-content: center; align-items: center;">
            <img src="<?php echo !empty($post['image_url']) ? 'uploads/' . htmlspecialchars($post['image_url']) : 'images/default_banner.png'; ?>" alt="Content Image Banner" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <div class="post-header" style="display: flex; justify-content: space-between; align-items: center; padding: 10px;">
            <div class="user-info" style="display: flex; align-items: center; gap: 10px;">
                <img src="<?php echo !empty($post['profile_picture']) && file_exists('uploads/' . $post['profile_picture']) ? 'uploads/' . htmlspecialchars($post['profile_picture']) : 'images/default_user.png'; ?>" alt="User Profile Picture" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                <div>
                    <a href="user_profile.php?username=<?php echo urlencode($post['author_username']); ?>" style="text-decoration: none; color: Black;">
                        <p class="username" style="margin: 0; font-size: 14px; font-weight: bold;"><?php echo htmlspecialchars($post['author_username']); ?></p>
                    </a>
                    <p style="margin: 0; font-size: 12px; color: #666;"><?php echo htmlspecialchars($post['title']); ?></p>
                </div>
            </div>
            <div class="post-meta" style="text-align: right; font-size: 12px; color: #666;">
                <p style="margin: 0;">Approved by: 
                    <?php echo htmlspecialchars($post['approved_by_username'] ?? 'N/A'); ?>
                </p>
                <p style="margin: 0;">Create date: <?php echo htmlspecialchars($post['created_at']); ?></p>
            </div>
        </div>
        <div class="post-content" style="padding: 15px; font-size: 14px; line-height: 1.6; color: #555;">
            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
        </div>
        <?php if ($isAdmin): ?>
            <form action="delete_post.php" method="POST" style="text-align: right; margin-top: 10px;">
                <input type="hidden" name="blog_id" value="<?php echo $post['blog_id']; ?>">
                <button type="submit" style="background-color: #ff4d4d; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer;">Remove</button>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
?>