<?php
require_once '../config/db.php';
require_once '../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: quizzes.php');
    exit;
}

$quiz_id = (int)($_POST['quiz_id'] ?? 0);
$answersInput = $_POST['answers'] ?? [];

if ($quiz_id <= 0) {
    die("Invalid quiz.");
}

// fetch quiz, questions, options
$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    die("Quiz not found.");
}

$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();
$questionMap = [];
$total_points = 0;
foreach ($questions as $q) {
    $questionMap[$q['id']] = $q;
    $total_points += (int)$q['points'];
}

$question_ids = array_keys($questionMap);
$optionsByQuestion = [];
if ($question_ids) {
    $stmt = $pdo->prepare("SELECT * FROM options WHERE question_id IN (" . implode(',', array_fill(0, count($question_ids), '?')) . ")");
    $stmt->execute($question_ids);
    foreach ($stmt->fetchAll() as $opt) {
        $optionsByQuestion[$opt['question_id']][] = $opt;
    }
}

$score = 0;
$answersSaved = [];
$user_id = current_user_id();

$started_at = date('Y-m-d H:i:s', time() - 60); // naive: assume started 1 minute ago
$completed_at = date('Y-m-d H:i:s');
$duration_seconds = 60; // you can later send exact duration via hidden field

// grade
foreach ($answersInput as $question_id => $val) {
    $question_id = (int)$question_id;
    if (!isset($questionMap[$question_id])) continue;
    $q = $questionMap[$question_id];
    $is_correct = 0;
    $selected_option_id = null;
    $answer_text = null;

    if ($q['question_type'] === 'mcq') {
        $selected_option_id = (int)$val;
        foreach ($optionsByQuestion[$question_id] ?? [] as $opt) {
            if ($opt['id'] == $selected_option_id && $opt['is_correct']) {
                $is_correct = 1;
                break;
            }
        }
    } else { // short answer: simple case-insensitive exact match with correct option (first correct option)
        $answer_text = trim($val);
        $correct_answer = null;
        foreach ($optionsByQuestion[$question_id] ?? [] as $opt) {
            if ($opt['is_correct']) {
                $correct_answer = $opt['option_text'];
                break;
            }
        }
        if ($correct_answer !== null && strcasecmp($answer_text, $correct_answer) === 0) {
            $is_correct = 1;
        }
    }

    if ($is_correct) {
        $score += (int)$q['points'];
    }

    $answersSaved[] = [
        'question_id' => $question_id,
        'selected_option_id' => $selected_option_id,
        'answer_text' => $answer_text,
        'is_correct' => $is_correct
    ];
}

// save attempt and answers (transaction)
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare("INSERT INTO attempts (user_id, quiz_id, score, total_points, started_at, completed_at, duration_seconds)
                           VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $quiz_id, $score, $total_points, $started_at, $completed_at, $duration_seconds]);
    $attempt_id = $pdo->lastInsertId();

    $stmtAns = $pdo->prepare("INSERT INTO answers (attempt_id, question_id, selected_option_id, answer_text, is_correct)
                              VALUES (?, ?, ?, ?, ?)");
    foreach ($answersSaved as $a) {
        $stmtAns->execute([
            $attempt_id,
            $a['question_id'],
            $a['selected_option_id'],
            $a['answer_text'],
            $a['is_correct']
        ]);
    }

    // basic badge example: quizzes_taken >= 5
    $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM attempts WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $cnt = (int)$stmt->fetch()['cnt'];

    $stmt = $pdo->prepare("SELECT * FROM badges WHERE criteria_type = 'quizzes_taken' AND criteria_value <= ?");
    $stmt->execute([$cnt]);
    $eligibleBadges = $stmt->fetchAll();

    foreach ($eligibleBadges as $badge) {
        // check if already has
        $check = $pdo->prepare("SELECT 1 FROM user_badges WHERE user_id = ? AND badge_id = ?");
        $check->execute([$user_id, $badge['id']]);
        if (!$check->fetch()) {
            $ins = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
            $ins->execute([$user_id, $badge['id']]);
        }
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die("Error saving attempt: " . htmlspecialchars($e->getMessage()));
}

// feedback page
include '../includes/header.php';
?>

<h1>Quiz Result</h1>

<p>You scored <strong><?= $score ?></strong> out of <strong><?= $total_points ?></strong>.</p>
<p>Percentage: <strong><?= round(($score / max(1, $total_points)) * 100, 2) ?>%</strong></p>

<a class="btn" href="quizzes.php">Back to Quizzes</a>
<a class="btn secondary" href="profile.php">View Progress</a>

<?php include '../includes/footer.php'; ?>
