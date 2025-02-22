<?php
require_once(__DIR__ . '/../../config.php');

$eval_id = required_param('eval_id', PARAM_INT);
$grade = required_param('grade', PARAM_INT);

$record = new stdClass();
$record->id = $eval_id;
$record->grade = $grade;

if ($DB->update_record('studentqcm_evaluation', $record)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
?>
