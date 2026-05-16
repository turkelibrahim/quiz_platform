<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_admin();

$errors = [];
$success = "";

// Handle Delete/Toggle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($_GET['action'] === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Quiz deleted successfully.";
    } elseif ($_GET['action'] === 'toggle') {
        $stmt = $pdo->prepare("UPDATE quizzes SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        $success = "Quiz status updated.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $time_limit = (int)($_POST['time_limit'] ?? 0);

    if ($title === '' || $subject === '' || $time_limit <= 0) {
        $errors[] = "Title, subject and time limit are required.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO quizzes (title, description, subject, time_limit, created_by)
                               VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $description, $subject, $time_limit, current_user_id()]);
        $quiz_id = $pdo->lastInsertId();
        header('Location: admin_questions.php?quiz_id=' . $quiz_id);
        exit;
    }
}

$stmt = $pdo->query("SELECT * FROM quizzes ORDER BY created_at DESC");
$quizzes = $stmt->fetchAll();

$stmtUsers = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmtUsers->fetchColumn();

include '../includes/header.php';
?>

<div class="dashboard-header">
    <h1>Admin Panel: Quiz Management</h1>
    <p class="text-muted">Create, edit, and manage your platform's quizzes.</p>
</div>

<?php if ($success): ?>
    <div class="card" style="background: #dcfce7; color: #166534; border: 1px solid #22c55e; padding: 1rem; margin-bottom: 1.5rem; border-radius: 12px;">
        ✅ <?= htmlspecialchars($success) ?>
    </div>
<?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-value"><?= count($quizzes) ?></span>
        <span class="stat-label">Total Quizzes</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= count(array_filter($quizzes, fn($q) => $q['is_active'])) ?></span>
        <span class="stat-label">Active Quizzes</span>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= $total_users ?></span>
        <span class="stat-label">Total Users</span>
    </div>
</div>

<div class="dashboard-content">
    <section class="card form-card">
        <h2>➕ Create New Quiz</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div style="grid-column: span 2;">
                <label>Quiz Title</label>
                <input type="text" name="title" placeholder="e.g. Advanced Calculus" required>
            </div>
            <div>
                <label>Subject Category</label>
                <select name="subject" required>
                    <option value="Math">Math</option>
                    <option value="Science">Science</option>
                    <option value="General Knowledge">General Knowledge</option>
                </select>
            </div>
            <div>
                <label>Time Limit (seconds)</label>
                <input type="number" name="time_limit" min="30" value="300" required>
            </div>
            <div style="grid-column: span 2;">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Briefly describe what this quiz covers..."></textarea>
            </div>
            <div style="grid-column: span 2; margin-top: 0.5rem;">
                <button type="submit" class="btn">Create Quiz & Add Questions</button>
            </div>
        </form>
    </section>

    <section class="card">
        <h2>📋 Existing Quizzes</h2>
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                <tr>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Time Limit</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($quizzes as $q): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($q['title']) ?></strong></td>
                        <td><span class="meta"><?= htmlspecialchars($q['subject']) ?></span></td>
                        <td><?= (int)$q['time_limit'] ?>s</td>
                        <td>
                            <a href="?action=toggle&id=<?= $q['id'] ?>" style="text-decoration: none;">
                                <span style="display: inline-block; padding: 0.25rem 0.5rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; background: <?= $q['is_active'] ? '#dcfce7' : '#fee2e2' ?>; color: <?= $q['is_active'] ? '#166534' : '#991b1b' ?>; cursor: pointer;">
                                    <?= $q['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
                                </span>
                            </a>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem;">
                                <a href="admin_questions.php?quiz_id=<?= $q['id'] ?>" class="btn secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                    Questions
                                </a>
                                <a href="?action=delete&id=<?= $q['id'] ?>" class="btn secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; border-color: #ef4444; color: #ef4444;" onclick="return confirm('Are you sure you want to delete this quiz?')">
                                    Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
