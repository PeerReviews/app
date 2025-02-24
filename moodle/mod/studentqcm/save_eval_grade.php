<?php
require_once(__DIR__.'/../../config.php');

$eval_id = required_param('eval_id', PARAM_INT);
$grade = required_param('grade', PARAM_INT);

$evaluation = $DB->get_record('studentqcm_evaluation', array('id' => $eval_id));
if ($evaluation) {
    $evaluation->grade = $grade;
    $DB->update_record('studentqcm_evaluation', $evaluation);
    
    $user_evaluations = $DB->get_records('studentqcm_evaluation', array('question_id' => $evaluation->question_id, 'userid' => $evaluation->userid));

    $evaluated = count(array_filter($user_evaluations, function($e) {
        return $e->grade !== null;
    }));
    
    $total = count($user_evaluations);

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
