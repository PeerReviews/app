<?php
namespace mod_studentqcm\task;

use core\task\scheduled_task;

class attribution_teacher_task extends scheduled_task {

    public function get_name() {
        return "Attribution automatique des productions aux étudiants";
    }

    public function execute() {
        global $DB;

        $records = $DB->get_records('studentqcm', null, 'id DESC', '*', 0, 1);
        $studentqcm = reset($records); // Prendre le premier élément

        if (!$studentqcm || empty($studentqcm->end_date_tt_3)) {
            mtrace("Erreur : date de lancement introuvable.");
            die();
        }

        // Vérifie si la tâche a déjà été effectuée
        if ($studentqcm->attribution_teacher_completed == 1) {
            mtrace("La tâche a déjà été effectuée.");
            die();  // Si déjà effectuée, on arrête l'exécution
        }

        $start_timestamp = $studentqcm->end_date_tt_3;
        $current_timestamp = time();

        if ($current_timestamp < $start_timestamp) {
            mtrace("La date de lancement n'est pas encore atteinte. Attente...");
            die();
        }

        // Exécution de l'attribution automatique
        mtrace("Déclenchement de l'attribution automatique !");

        // Récupération des enseignants
        $teachers = $DB->get_records('teachers', null, '', 'id');
        if (empty($teachers)) {
            mtrace("Erreur : aucun enseignant trouvé.");
            die();
        }

        // Récupération des étudiants
        $students = $DB->get_records('students', null, '', 'id');
        if (empty($students)) {
            mtrace("Erreur : aucun étudiant trouvé.");
            die();
        }

        $teacher_ids = array_keys($teachers);
        $num_teachers = count($teacher_ids);
        $assignments = [];

        if ($num_teachers === 0) {
            mtrace("Erreur : aucun enseignant disponible.");
            die();
        }

        $index = 0;
        foreach ($students as $student) {
            $teacher_id = $teacher_ids[$index % $num_teachers]; // Attribution équilibrée
            $assignments[] = [
                'userid' => $student->id,
                'teacherid' => $teacher_id
            ];
            $index++;
        }

        // Insérer les attributions dans la table `pr_assigned_student_teacher`
        foreach ($assignments as $assignment) {
            $record = new \stdClass();
            $record->userid = $assignment['student_id'];
            $record->teacherid = $assignment['teacher_id'];

            $DB->insert_record('pr_assigned_student_teacher', $record);
        }

        // Marquer la tâche comme terminée
        $studentqcm->attribution_teacher_completed = 1;
        $DB->update_record('studentqcm', $studentqcm);
        mtrace("Attribution des productions terminée !");
    }
}
