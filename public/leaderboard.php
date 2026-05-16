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

<h1>Leaderboard</h1>

<table class="table">
    <thead>
    <tr>
        <th>#</th>
        <th>User</th>
        <th>Best Score %</th>
        <th>Quizzes Taken</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $i => $r): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($r['username']) ?></td>
            <td><?= round($r['best_percent'], 2) ?>%</td>
            <td><?= (int)$r['quizzes_taken'] ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
