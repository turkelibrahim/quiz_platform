<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_login();
include '../includes/header.php';

$user_id = current_user_id();

$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// attempts history
$stmt = $pdo->prepare("
    SELECT a.*, q.title
    FROM attempts a
    JOIN quizzes q ON a.quiz_id = q.id
    WHERE a.user_id = ?
    ORDER BY a.completed_at DESC
");
$stmt->execute([$user_id]);
$attempts = $stmt->fetchAll();

// badges
$stmt = $pdo->prepare("
    SELECT b.*
    FROM user_badges ub
    JOIN badges b ON ub.badge_id = b.id
    WHERE ub.user_id = ?
");
$stmt->execute([$user_id]);
$badges = $stmt->fetchAll();
?>

<h1>My Profile</h1>

<section class="card">
    <h2><?= htmlspecialchars($user['username']) ?></h2>
    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
    <p>Member since: <?= htmlspecialchars($user['created_at']) ?></p>
</section>

<section class="card">
    <h2>Progress</h2>
    <?php if (!$attempts): ?>
        <p>No quizzes taken yet.</p>
    <?php else: ?>
        <table class="table">
            <thead>
            <tr>
                <th>Quiz</th>
                <th>Score</th>
                <th>Percentage</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($attempts as $a): ?>
                <tr>
                    <td><?= htmlspecialchars($a['title']) ?></td>
                    <td><?= (int)$a['score'] ?>/<?= (int)$a['total_points'] ?></td>
                    <td><?= round(($a['score'] / max(1, $a['total_points'])) * 100, 2) ?>%</td>
                    <td><?= htmlspecialchars($a['completed_at']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>

<section class="card">
    <h2>Badges</h2>
    <?php if (!$badges): ?>
        <p>No badges yet. Keep taking quizzes!</p>
    <?php else: ?>
        <div class="badge-grid">
            <?php foreach ($badges as $b): ?>
                <div class="badge">
                    <div class="badge-icon"><?= htmlspecialchars($b['icon'] ?? '🏅') ?></div>
                    <div class="badge-text">
                        <strong><?= htmlspecialchars($b['name']) ?></strong>
                        <p><?= htmlspecialchars($b['description']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php include '../includes/footer.php'; ?>
