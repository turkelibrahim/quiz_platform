<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_admin();
include '../includes/header.php';

$quiz_id = (int)($_GET['quiz_id'] ?? 0);
if ($quiz_id <= 0) {
    echo "Invalid quiz.";
    include '../includes/footer.php'; exit;
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    echo "Quiz not found.";
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

<h1>Questions for: <?= htmlspecialchars($quiz['title']) ?></h1>

<h2>Add Question</h2>

<?php if (!empty($errors)): ?>
    <div class="alert error">
        <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="post" class="card form-card" id="question-form">
    <label>Question Text
        <textarea name="question_text" rows="3" required></textarea>
    </label>
    <label>Points
        <input type="number" name="points" min="1" value="1">
    </label>
    <label>Question Type
        <select name="question_type" id="question_type">
            <option value="mcq">Multiple Choice</option>
            <option value="short">Short Answer</option>
        </select>
    </label>

    <div id="mcq-options">
        <p>Options (leave blank to ignore):</p>
        <?php for ($i = 0; $i < 4; $i++): ?>
            <label>Option <?= $i + 1 ?>
                <input type="text" name="options[<?= $i ?>]">
                <input type="radio" name="correct_option" value="<?= $i ?>"> Correct
            </label>
        <?php endfor; ?>
    </div>

    <button type="submit">Add Question</button>
</form>

<h2>Existing Questions</h2>
<ol>
    <?php foreach ($questions as $q): ?>
        <li>
            <?= htmlspecialchars($q['question_text']) ?> (<?= htmlspecialchars($q['question_type']) ?>, <?= (int)$q['points'] ?> pts)
        </li>
    <?php endforeach; ?>
</ol>

<?php include '../includes/footer.php'; ?>
