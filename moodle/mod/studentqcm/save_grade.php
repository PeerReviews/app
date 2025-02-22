<?php
require_once(__DIR__ . '/../../config.php');

$qcm_id = required_param('qcm_id', PARAM_INT);
$grade = required_param('grade', PARAM_INT);

$record = new stdClass();
$record->id = $qcm_id;
$record->grade = $grade;

if ($DB->update_record('studentqcm_question', $record)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error']);
}
?>
