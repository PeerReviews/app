<?php
require_once('../../config.php'); // Inclure la config de Moodle
global $DB;

$id = required_param('id', PARAM_INT);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (isset($_POST['save_teachers']) && $_POST['save_teachers'] != ""){
        foreach ($_POST['save_teachers'] as $teacher_id => $teacherData) {

            $teacherid = isset($teacherData['teacher_id']) ? intval($teacherData['teacher_id']) : null;
            $userid = isset($teacherData['student_id']) ? intval($teacherData['student_id']) : null;

            $record = new stdClass();
            $record->teacherid = $teacherid;
            $record->userid = $userid;

            $attribution_id = $DB->insert_record('pr_assigned_student_teacher', $record);
            if (!$attribution_id) {
                // En cas d'erreur
                redirect(new moodle_url('/mod/studentqcm/view.php', array('id' => $id)));
            }
        }
    }

    if (isset($_POST['updatedData']) && $_POST['updatedData'] != "") {
        foreach ($_POST['updatedData'] as $updated_id => $updated) {

            $updatedRow = json_decode($updated, true);

            $row_id = isset($updatedRow['id']) ? intval($updatedRow['id']) : null;
            $teacherid = isset($updatedRow['teacherId']) ? intval($updatedRow['teacherId']) : null;
            $userid = isset($updatedRow['studentId']) ? intval($updatedRow['studentId']) : null;

            // Vérifier si l'étudiant existe déjà dans la base de données
            $existingRecord = $DB->get_record('pr_assigned_student_teacher', array('id' => $row_id, 'teacherid' => $teacherid));
    
            if ($existingRecord) {
                // Mise à jour des enregistrements existants
                $existingRecord->teacherid = $teacherid;
                $existingRecord->userid = $userid;
    
                // Effectuer la mise à jour
                $updated = $DB->update_record('pr_assigned_student_teacher', $existingRecord);
    
                if (!$updated) {
                    print_r("Echec de l'update");
                    // En cas d'échec de la mise à jour
                    redirect(new moodle_url('/mod/studentqcm/view.php', array('id' => $id)), '', 0);
                }
            } 
        }
    }

    redirect(new moodle_url('/mod/studentqcm/manual_attribution.php?gestion=teacher', array('id' => $id)), '', 0);
}
else {
    // Redirection si l'accès est direct
    redirect(new moodle_url('/mod/studentqcm/view.php', array('id' => $id)), '', 0);
}
