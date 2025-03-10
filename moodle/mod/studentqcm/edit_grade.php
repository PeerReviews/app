<?php
require_once(__DIR__ . '/../../config.php');
global $DB;

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
require_login($course, true, $cm);

// Récupération des variables envoyées via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['updatedData'])) {

        foreach ($_POST['updatedData'] as $data) {
            $data = json_decode($data, true); // Décoder les données JSON

            $studentid = isset($data['studentId']) ? intval($data['studentId']) : null;
            $total_grade_questions = isset($data['grade1']) ? intval($data['grade1']) : null;
            $total_grade_revisions = isset($data['grade2']) ? intval($data['grade2']) : null;

            // Vérification que les données nécessaires sont présentes
            if ($studentid !== null) {
                // Préparer la requête pour récupérer la ligne avec $studentid
                $student_grade = $DB->get_record('pr_grade', array('userid' => $studentid));

                if ($student_grade) {
                    // Si l'étudiant existe, mettre à jour les valeurs
                    $student_grade->production_grade = $total_grade_questions;
                    $student_grade->revision_grade = $total_grade_revisions;
                    $DB->update_record('pr_grade', $student_grade);
                }
            }
        }
    }
}

redirect(new moodle_url('/mod/studentqcm/admin_grade_gestion.php?', array('id' => $id)), '', 0);

?>
