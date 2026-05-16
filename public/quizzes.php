<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
include '../includes/header.php';

$stmt = $pdo->query("SELECT q.*, u.username AS creator
                     FROM quizzes q
                     JOIN users u ON q.created_by = u.id
                     WHERE q.is_active = 1
                     ORDER BY q.created_at DESC");
$quizzes = $stmt->fetchAll();
?>

<h1>Available Quizzes</h1>

<div class="quiz-grid">
    <?php foreach ($quizzes as $quiz): ?>
        <div class="card quiz-card">
            <h2><?= htmlspecialchars($quiz['title']) ?></h2>
            <p class="meta"><?= htmlspecialchars($quiz['subject']) ?> &middot; 
               Time limit: <?= (int)$quiz['time_limit'] ?>s</p>
            <p><?= nl2br(htmlspecialchars($quiz['description'])) ?></p>
            <p class="creator">By <?= htmlspecialchars($quiz['creator']) ?></p>
            <a class="btn" href="take_quiz.php?id=<?= $quiz['id'] ?>">Take Quiz</a>
        </div>
    <?php endforeach; ?>
</div>

<?php include '../includes/footer.php'; ?>
