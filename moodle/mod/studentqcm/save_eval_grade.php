<?php
require_once(__DIR__.'/../../config.php');

header('Content-Type: application/json'); // Forcer la réponse en JSON
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$eval_id = required_param('eval_id', PARAM_INT);
$grade = required_param('grade', PARAM_INT);
$prod_id = required_param('prod_id', PARAM_INT);

$userid = $USER->id;

$evaluation = $DB->get_record('studentqcm_evaluation', array('id' => $eval_id));
if ($evaluation) {
    $evaluation->grade = $grade;
    $DB->update_record('studentqcm_evaluation', $evaluation);

    $questions = $DB->get_records('studentqcm_question', array('userid' => $prod_id));

    // Trier pour récupérer les évaluations données par $reviewer
    $evaluations = array();
    $reviewer = $evaluation->userid;

    foreach ($questions as $question) {
        $eval = $DB->get_record('studentqcm_evaluation', array('question_id' => $question->id, 'userid' => $reviewer));
        if ($eval) {
            $evaluations[] = $eval;
        }
    }

    $evaluated = count(array_filter($evaluations, function($e) {
        return isset($e->grade) && $e->grade !== null;
    }));

    $total = count($evaluations); // Correction ici

    echo json_encode([
        'status' => 'success',
        'user_id' => $evaluation->userid,
        'evaluated' => $evaluated,
        'total' => $total
    ]);
} else {
    echo json_encode(['status' => 'error']);
}
?>
