<?php
require_once '../config/db.php';
include '../includes/header.php';
?>

<section class="hero">
    <h1>Welcome to QuizPlatform</h1>
    <p>Take quizzes in Math, Science, and General Knowledge. Track your progress and earn badges!</p>
    <div class="hero-actions">
        <a class="btn" href="quizzes.php">Browse Quizzes</a>
        <?php if (empty($_SESSION['user_id'])): ?>
            <a class="btn secondary" href="register.php">Get Started</a>
        <?php endif; ?>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
