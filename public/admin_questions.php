<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_admin();
include '../includes/header.php';

$quiz_id = (int)($_GET['quiz_id'] ?? 0);
if ($quiz_id <= 0) {
    echo "<div class='alert error'>Invalid quiz.</div>";
    include '../includes/footer.php'; exit;
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    echo "<div class='alert error'>Quiz not found.</div>";
    include '../includes/footer.php'; exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = trim($_POST['question_text'] ?? '');
    $question_type = $_POST['question_type'] ?? 'mcq';
    $points = (int)($_POST['points'] ?? 1);

    if ($question_text === '') {
        $errors[] = "Question text is required.";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question_text, question_type, points)
                               VALUES (?, ?, ?, ?)");
        $stmt->execute([$quiz_id, $question_text, $question_type, $points]);
        $question_id = $pdo->lastInsertId();

        if ($question_type === 'mcq') {
            $options = $_POST['options'] ?? [];
            $correct_index = (int)($_POST['correct_option'] ?? -1);

            foreach ($options as $i => $opt) {
                $opt_text = trim($opt);
                if ($opt_text === '') continue;
                $is_correct = ($i == $correct_index) ? 1 : 0;
                $stmtOpt = $pdo->prepare("INSERT INTO options (question_id, option_text, is_correct)
                                          VALUES (?, ?, ?)");
                $stmtOpt->execute([$question_id, $opt_text, $is_correct]);
            }
        }
        header('Location: admin_questions.php?quiz_id=' . $quiz_id);
        exit;
    }
}

$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
?>

<div class="dashboard-header">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Manage Questions</h1>
            <p class="text-muted">Quiz: <strong><?= htmlspecialchars($quiz['title']) ?></strong></p>
        </div>
        <a href="admin_quizzes.php" class="btn secondary">⬅ Back to Quizzes</a>
    </div>
</div>

<div class="dashboard-content">
    <section class="card form-card">
        <h2>➕ Add New Question</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" id="question-form">
            <div style="margin-bottom: 1rem;">
                <label>Question Text</label>
                <textarea name="question_text" rows="3" placeholder="Enter the question here..." required></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <div>
                    <label>Points</label>
                    <input type="number" name="points" min="1" value="1">
                </div>
                <div>
                    <label>Type</label>
                    <select name="question_type" id="question_type">
                        <option value="mcq">Multiple Choice</option>
                        <option value="short">Short Answer</option>
                    </select>
                </div>
            </div>

            <div id="mcq-options" class="card" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                <p style="font-weight: 600; font-size: 0.9rem; margin-top: 0;">Options (Mark the correct one)</p>
                <div style="display: grid; gap: 0.75rem;">
                    <?php for ($i = 0; $i < 4; $i++): ?>
                        <div style="display: flex; align-items: center; gap: 1rem; background: white; padding: 0.5rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <input type="radio" name="correct_option" value="<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?>>
                            <input type="text" name="options[<?= $i ?>]" placeholder="Option <?= $i + 1 ?>" style="margin-top: 0; border: none; padding: 0.25rem;">
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn">Add Question</button>
            </div>
        </form>
    </section>

    <section class="card">
        <h2>📝 Existing Questions (<?= count($questions) ?>)</h2>
        <?php if (!$questions): ?>
            <p class="text-muted">No questions added yet.</p>
        <?php else: ?>
            <div style="display: grid; gap: 1rem;">
                <?php foreach ($questions as $index => $q): ?>
                    <div class="card" style="margin-bottom: 0; border-left: 4px solid var(--primary);">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                            <div>
                                <span class="meta" style="font-weight: 600;">QUESTION #<?= $index + 1 ?></span>
                                <p style="margin: 0.5rem 0; font-weight: 500; font-size: 1.1rem;"><?= htmlspecialchars($q['question_text']) ?></p>
                                <span class="badge" style="font-size: 0.75rem; padding: 0.2rem 0.5rem; background: #f1f5f9; border-radius: 4px;">
                                    <?= strtoupper($q['question_type']) ?> • <?= (int)$q['points'] ?> PTS
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php include '../includes/footer.php'; ?>
