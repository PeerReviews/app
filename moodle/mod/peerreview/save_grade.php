<?php
require_once(__DIR__ . '/../../config.php');

$qcm_id = required_param('qcm_id', PARAM_INT);
$grade = required_param('grade', PARAM_INT);

$question = $DB->get_record('pr_question', ['id' => $qcm_id], '*', MUST_EXIST);
$prod_id = $question->userid;

$record = new stdClass();
$record->id = $qcm_id;
$record->grade = $grade;

$response = [];

if ($DB->update_record('pr_question', $record)) {
    // Recalculer le nombre de questions évaluées pour cette production
    $nb_eval_questions = $DB->count_records_select(
        'pr_question',
        "grade IS NOT NULL AND userid = ?",
        [$prod_id]
    );

    $response = [
        'status' => 'success',
        'nb_eval_questions' => $nb_eval_questions
    ];
} else {
    $response = ['status' => 'error'];
}

echo json_encode($response);
?>
