<?php
namespace mod_studentqcm\task;

use core\task\scheduled_task;

class attribution_student_task extends scheduled_task {

    public function get_name() {
        return "Attribution automatique des productions aux étudiants";
    }

    public function execute(bool $force = false) {
        global $DB;

        $studentqcm = $DB->get_record('studentqcm', ['archived' => 0]);

        if (!$studentqcm || empty($studentqcm->start_date_2)) {
            mtrace("Erreur : date de lancement introuvable.");
            die();
        }

        // Vérifie si la tâche a déjà été effectuée
        if (!$force && $studentqcm->attribution_student_completed == 1) {
            mtrace("L'attribution automatique a déjà été effectuée.");
            die();  // Si déjà effectuée, on arrête l'exécution
        }


        $current_timestamp = time(); 
        $start_timestamp = $studentqcm->start_date_2;

        if ($current_timestamp < $start_timestamp) {
            mtrace("La date d'ouverture de la phase 2 n'est pas encore atteinte.");
            die();
        }

        // Exécution de l'attribution automatique

        // Supprimer les anciennes attributions
        $DB->delete_records('studentqcm_assignedqcm');
        $DB->execute("ALTER TABLE {studentqcm_assignedqcm} AUTO_INCREMENT = 1");

        $students = $DB->get_records('user', null, '', 'id');
        $student_ids = array_keys($students);
        $num_students = count($student_ids);

        $nbReviewers = $studentqcm->nbreviewers;

        if ($num_students < 2 || $nbReviewers >= $num_students) {
            mtrace("Il faut au moins 2 étudiants et nbReviewers < nombre d'étudiants !");
            die();
        }

        $remaining_assignments = array_fill_keys($student_ids, $nbReviewers);

        foreach ($student_ids as $student_id) {
            $assignments = [];

            // Liste des étudiants disponibles (pas soi-même et max 3 assignations)
            $possible_assignees = array_values(array_filter($student_ids, function ($id) use ($student_id, $remaining_assignments) {
                return $id !== $student_id && ($remaining_assignments[$id] ?? 0) > 0;
            }));

            shuffle($possible_assignees);

            $assigned_students = array_slice($possible_assignees, 0, $nbReviewers);

            // Stocker les attributions
            $record = new \stdClass();
            $record->user_id = $student_id;
            $record->prod1_id = $assigned_students[0] ?? null;
            $record->prod2_id = $assigned_students[1] ?? null;
            $record->prod3_id = $assigned_students[2] ?? null;

            $DB->insert_record('studentqcm_assignedqcm', $record);

            // Mettre à jour le compteur d'assignations
            foreach ($assigned_students as $assignee) {
                $remaining_assignments[$assignee] = ($remaining_assignments[$assignee] ?? 0) - 1;
            }
        }

        mtrace("Attribution des productions terminée !");

    }
}
