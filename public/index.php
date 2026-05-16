<?php
require_once '../config/db.php';
include '../includes/header.php';
?>

<section class="hero">
    <h1>Master Any Subject with <span style="color: var(--primary);">QuizPlatform</span></h1>
    <p>Test your knowledge, compete with others, and level up your skills in Math, Science, and beyond. Interactive, fun, and completely free.</p>
    <div class="hero-actions">
        <a class="btn" href="quizzes.php">🚀 Explore Quizzes</a>
        <?php if (empty($_SESSION['user_id'])): ?>
            <a class="btn secondary" href="register.php">✨ Create Free Account</a>
        <?php endif; ?>
    </div>
</section>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-value">1,000+</span>
        <span class="stat-label">Questions</span>
    </div>
    <div class="stat-card">
        <span class="stat-value">50+</span>
        <span class="stat-label">Active Quizzes</span>
    </div>
    <div class="stat-card">
        <span class="stat-value">24/7</span>
        <span class="stat-label">Learning</span>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
