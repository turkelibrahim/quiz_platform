<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_admin();
include '../includes/header.php';

$errors = [];
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
?>

<h1>Admin: Manage Quizzes</h1>

<h2>Create New Quiz</h2>

<?php if (!empty($errors)): ?>
    <div class="alert error">
        <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="card form-card">
    <label>Title
        <input type="text" name="title" required>
    </label>
    <label>Subject
        <select name="subject" required>
            <option value="Math">Math</option>
            <option value="Science">Science</option>
            <option value="General Knowledge">General Knowledge</option>
        </select>
    </label>
    <label>Time Limit (seconds)
        <input type="number" name="time_limit" min="30" required>
    </label>
    <label>Description
        <textarea name="description" rows="3"></textarea>
    </label>
    <button type="submit">Create Quiz</button>
</form>

<h2>Existing Quizzes</h2>
<table class="table">
    <thead>
    <tr>
        <th>Title</th>
        <th>Subject</th>
        <th>Time Limit</th>
        <th>Active</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($quizzes as $q): ?>
        <tr>
            <td><?= htmlspecialchars($q['title']) ?></td>
            <td><?= htmlspecialchars($q['subject']) ?></td>
            <td><?= (int)$q['time_limit'] ?>s</td>
            <td><?= $q['is_active'] ? 'Yes' : 'No' ?></td>
            <td>
                <a href="admin_questions.php?quiz_id=<?= $q['id'] ?>">Questions</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<?php include '../includes/footer.php'; ?>
