<?php

require_once(__DIR__ . '/../../config.php');
global $DB;

// Récupération des variables envoyées via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $studentid = isset($_POST['studentid']) ? $_POST['studentid'] : null;
    $total_grade_questions = isset($_POST['total_grade_questions']) ? $_POST['total_grade_questions'] : null;
    $total_grade_revisions = isset($_POST['total_grade_revisions']) ? $_POST['total_grade_revisions'] : null;

    $DB->insert_record('pr_grade');

    // Vérification que les données nécessaires sont présentes
    if ($studentid !== null) {
        // Préparer la requête pour récupérer la ligne avec $studentid
        $student_grade = $DB->get_record('pr_grade', array('userid' => $studentid));

        if ($student_grade) {
            // Si l'étudiant existe, mettre à jour les valeurs
            $student_grade->total_grade_questions = $total_grade_questions;
            $student_grade->total_grade_revisions = $total_grade_revisions;

            // Mettre à jour la base de données
            if ($DB->update_record('pr_grade', $student_grade)) {
                // Si la mise à jour réussit
                echo "Les notes de l'étudiant ont été mises à jour avec succès.";
            } else {
                // Si la mise à jour échoue
                echo "Une erreur est survenue lors de la mise à jour des notes.";
            }
        } else {
            // Si l'étudiant n'existe pas
            echo "Aucun étudiant trouvé avec cet identifiant.";
        }
    } 
    redirect(new moodle_url('/mod/studentqcm/admin_grade_gestion.php', array('id' => $id)));
}


?>