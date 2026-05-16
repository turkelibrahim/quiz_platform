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

// Calculate simple stats
$total_quizzes = count($attempts);
$avg_score = 0;
if ($total_quizzes > 0) {
    $sum_pct = 0;
    foreach ($attempts as $a) {
        $sum_pct += ($a['score'] / max(1, $a['total_points'])) * 100;
    }
    $avg_score = round($sum_pct / $total_quizzes, 1);
}
?>

<div class="dashboard-header">
    <h1>Welcome back, <?= htmlspecialchars($user['username']) ?>! 👋</h1>
    <p class="text-muted">Here's what's happening with your learning progress.</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-value"><?= $total_quizzes ?></span>
        <span class="stat-label">Quizzes Taken</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= $avg_score ?>%</span>
        <span class="stat-label">Average Score</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= count($badges) ?></span>
        <span class="stat-label">Badges Earned</span>
    </div>
</div>

<div class="dashboard-content">
    <section class="card">
        <h2>📊 Recent Activity</h2>
        <?php if (!$attempts): ?>
            <p>No quizzes taken yet. <a href="quizzes.php" style="color: var(--primary); font-weight: 600;">Start your first quiz!</a></p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                    <tr>
                        <th>Quiz Title</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Completed At</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($attempts as $a): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($a['title']) ?></strong></td>
                            <td><?= (int)$a['score'] ?> / <?= (int)$a['total_points'] ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <?php $pct = round(($a['score'] / max(1, $a['total_points'])) * 100); ?>
                                    <div style="flex: 1; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; min-width: 60px;">
                                        <div style="height: 100%; width: <?= $pct ?>%; background: <?= $pct >= 70 ? '#22c55e' : ($pct >= 40 ? '#f59e0b' : '#ef4444') ?>;"></div>
                                    </div>
                                    <span><?= $pct ?>%</span>
                                </div>
                            </td>
                            <td><?= date('M d, Y', strtotime($a['completed_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>🏆 Your Badges</h2>
        <?php if (!$badges): ?>
            <p>No badges yet. Keep taking quizzes to unlock them!</p>
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
</div>

<?php include '../includes/footer.php'; ?>
