<?php
require_once('../../config.php'); // Inclure la config de Moodle
global $DB;

$id = required_param('id', PARAM_INT);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_students'])) {

    foreach ($_POST['save_students'] as $student_id => $studentData) {

        $student_id = isset($studentData['student_id']) ? intval($studentData['student_id']) : null;
        $prod1 = isset($studentData['prod1']) ? intval($studentData['prod1']) : null;
        $prod2 = isset($studentData['prod2']) ? intval($studentData['prod2']) : null;
        $prod3 = isset($studentData['prod3']) ? intval($studentData['prod3']) : null;

        $record = new stdClass();
        $record->user_id = $student_id;
        $record->prod1_id = $prod1;
        $record->prod2_id = $prod2;
        $record->prod3_id = $prod3 == 0 ? null : $prod3;

        $attribution_id = $DB->insert_record('studentqcm_assignedqcm', $record);
        if (!$attribution_id) {
            // En cas d'erreur
            redirect(new moodle_url('/mod/studentqcm/view.php', array('id' => $id)));
        }
    }
    redirect(new moodle_url('/mod/studentqcm/view.php', array('id' => $id)), '', 0);
}
else {
    // Redirection si l'accès est direct
    redirect(new moodle_url('/mod/studentqcm/view.php', array('id' => $id)), '', 0);
}
