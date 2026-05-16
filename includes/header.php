<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Platform</title>
    <link rel="stylesheet" href="assets/style.css">
    <script defer src="assets/app.js"></script>
</head>
<body>
<header class="top-nav">
    <div class="logo"><a href="index.php">QuizPlatform</a></div>
    <nav>
        <a href="quizzes.php">Quizzes</a>
        <a href="leaderboard.php">Leaderboard</a>
        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="profile.php">My Profile</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="admin_quizzes.php">Admin</a>
            <?php endif; ?>
            <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
