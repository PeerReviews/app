<?php
require_once('../../config.php'); // Inclure la config de Moodle
global $DB;

$id = required_param('id', PARAM_INT);
$session = $DB->get_record('studentqcm', ['archived' => 0], '*', MUST_EXIST);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['save_students']) && $_POST['save_students'] != ""){
        foreach ($_POST['save_students'] as $student_id => $studentData) {

            $student_id = isset($studentData['student_id']) ? intval($studentData['student_id']) : null;
            $prod1 = isset($studentData['prod1']) ? intval($studentData['prod1']) : null;
            $prod2 = isset($studentData['prod2']) ? intval($studentData['prod2']) : null;
            $prod3 = isset($studentData['prod3']) ? intval($studentData['prod3']) : null;

            $record = new stdClass();
            $record->user_id = $student_id;
            $record->sessionid = $session->id;
            $record->prod1_id = $prod1;
            $record->prod2_id = $prod2;
            $record->prod3_id = $prod3 == "" ? null : $prod3;

            $attribution_id = $DB->insert_record('studentqcm_assignedqcm', $record);
            if (!$attribution_id) {
                // En cas d'erreur
                redirect(new moodle_url('/mod/studentqcm/view.php', array('id' => $id)));
            }
        }
    }

    if (isset($_POST['updatedData']) && $_POST['updatedData'] != "") {
        foreach ($_POST['updatedData'] as $updated_id => $updated) {

            $updatedRow = json_decode($updated, true);

            $studentId = isset($updatedRow['studentId']) ? intval($updatedRow['studentId']) : null;
            $prod1 = isset($updatedRow['prod1']) ? intval($updatedRow['prod1']) : null;
            $prod2 = isset($updatedRow['prod2']) ? intval($updatedRow['prod2']) : null;
            $prod3 = isset($updatedRow['prod3']) ? intval($updatedRow['prod3']) : null;

            // Vérifier si l'étudiant existe déjà dans la base de données
            $existingRecord = $DB->get_record('studentqcm_assignedqcm', array('user_id' => $studentId, 'sessionid' => $session->id));
    
            if ($existingRecord) {
                // Mise à jour des enregistrements existants
                $existingRecord->prod1_id = $prod1;
                $existingRecord->prod2_id = $prod2;
                $existingRecord->prod3_id = ($prod3 == "" ? null : $prod3);
    
                // Effectuer la mise à jour
                $updated = $DB->update_record('studentqcm_assignedqcm', $existingRecord);
    
                if (!$updated) {
                    print_r("Echec de l'update");
                    // En cas d'échec de la mise à jour
                    redirect(new moodle_url('/mod/studentqcm/view.php', array('id' => $id)), '', 0);
                }
            } 
        }
    }

    redirect(new moodle_url('/mod/studentqcm/manual_attribution.php', array('id' => $id)), '', 0);
}
else {
    // Redirection si l'accès est direct
    redirect(new moodle_url('/mod/studentqcm/view.php', array('id' => $id)), '', 0);
}
