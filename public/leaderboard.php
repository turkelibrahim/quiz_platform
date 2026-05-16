<?php
require_once '../config/db.php';
include '../includes/header.php';

// Top performers by highest percentage score on any quiz
$stmt = $pdo->query("
    SELECT u.username,
           MAX(a.score / a.total_points) * 100 AS best_percent,
           COUNT(a.id) AS quizzes_taken
    FROM attempts a
    JOIN users u ON a.user_id = u.id
    GROUP BY u.id
    HAVING quizzes_taken > 0
    ORDER BY best_percent DESC
    LIMIT 20
");

$rows = $stmt->fetchAll();
?>

<div class="dashboard-header" style="text-align: center; margin-bottom: 3rem;">
    <h1>🏆 Global Leaderboard</h1>
    <p class="text-muted">The brightest minds on QuizPlatform. Can you make it to the top?</p>
</div>

<div class="card" style="padding: 0; overflow: hidden;">
    <table class="table">
        <thead style="background: #f8fafc;">
        <tr>
            <th style="width: 80px; text-align: center;">Rank</th>
            <th>Player</th>
            <th>Best Performance</th>
            <th>Total Quizzes</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $i => $r): ?>
            <tr style="<?= $i < 3 ? 'background: rgba(99, 102, 241, 0.03);' : '' ?>">
                <td style="text-align: center; font-weight: 800; font-size: 1.1rem;">
                    <?php if ($i == 0): ?> 🥇
                    <?php elseif ($i == 1): ?> 🥈
                    <?php elseif ($i == 2): ?> 🥉
                    <?php else: ?> <?= $i + 1 ?>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700;">
                            <?= strtoupper(substr($r['username'], 0, 1)) ?>
                        </div>
                        <span style="font-weight: 600;"><?= htmlspecialchars($r['username']) ?></span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="font-weight: 700; color: var(--primary);"><?= round($r['best_percent'], 1) ?>%</span>
                        <div style="width: 100px; height: 6px; background: #f1f5f9; border-radius: 3px; overflow: hidden;">
                            <div style="height: 100%; width: <?= $r['best_percent'] ?>%; background: var(--primary);"></div>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="meta" style="font-weight: 500; color: var(--text-main);"><?= (int)$r['quizzes_taken'] ?> quizzes</span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
