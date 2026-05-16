<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_login();
include '../includes/header.php';

$quiz_id = (int)($_GET['id'] ?? 0);
if ($quiz_id <= 0) {
    echo "Invalid quiz.";
    include '../includes/footer.php'; exit;
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND is_active = 1");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    echo "Quiz not found or inactive.";
    include '../includes/footer.php'; exit;
}

$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

if (!$questions) {
    echo "No questions in this quiz yet.";
    include '../includes/footer.php'; exit;
}

$question_ids = array_column($questions, 'id');
$stmt = $pdo->prepare("SELECT * FROM options WHERE question_id IN (" . implode(',', array_fill(0, count($question_ids), '?')) . ")");
$stmt->execute($question_ids);
$optionsRaw = $stmt->fetchAll();

$options = [];
foreach ($optionsRaw as $opt) {
    $options[$opt['question_id']][] = $opt;
}

// embed time limit for JS
$time_limit = (int)$quiz['time_limit'];
?>

<h1><?= htmlspecialchars($quiz['title']) ?></h1>
<p class="meta"><?= htmlspecialchars($quiz['subject']) ?> &middot; Time limit: <?= $time_limit ?> seconds</p>

<div class="timer" data-time-limit="<?= $time_limit ?>">
    Time remaining: <span id="timer-display"></span>
</div>

<form method="post" action="submit_quiz.php" class="card quiz-form" id="quiz-form">
    <input type="hidden" name="quiz_id" value="<?= $quiz['id'] ?>">
    <?php foreach ($questions as $index => $q): ?>
        <div class="question-block">
            <h3>Q<?= $index + 1 ?>. <?= htmlspecialchars($q['question_text']) ?> (<?= (int)$q['points'] ?> pts)</h3>
            <?php if ($q['question_type'] === 'mcq'): ?>
                <?php foreach ($options[$q['id']] ?? [] as $opt): ?>
                    <label class="option">
                        <input type="radio"
                               name="answers[<?= $q['id'] ?>]"
                               value="<?= $opt['id'] ?>">
                        <?= htmlspecialchars($opt['option_text']) ?>
                    </label>
                <?php endforeach; ?>
            <?php else: ?>
                <textarea name="answers[<?= $q['id'] ?>]" rows="2" placeholder="Your answer"></textarea>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <button type="submit">Submit Quiz</button>
</form>

<?php include '../includes/footer.php'; ?>
