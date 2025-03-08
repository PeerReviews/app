<?php
namespace mod_studentqcm\task;

use core\task\scheduled_task;

class attribution_student_task extends scheduled_task {

    public function get_name() {
        return "Attribution automatique des productions aux étudiants";
    }

    public function execute(bool $force = false) {
        global $DB;

        $records = $DB->get_records('studentqcm', null, 'id DESC', '*', 0, 1);
        $studentqcm = reset($records); // Prendre le premier élément

        if (!$studentqcm || empty($studentqcm->start_date_2)) {
            mtrace("Erreur : date de lancement introuvable.");
            die();
        }

        // Vérifie si la tâche a déjà été effectuée
        if (!$force && $studentqcm->attribution_student_completed == 1) {
            mtrace("L'attribution automatique a déjà été effectuée.");
            die();  // Si déjà effectuée, on arrête l'exécution
        }


        $start_timestamp = $studentqcm->start_date_2;
        $current_timestamp = time();

        if ($current_timestamp < $start_timestamp) {
            mtrace("La date de lancement n'est pas encore atteinte. Attente...");
            die();
        }

        // Exécution de l'attribution automatique

        // Supprimer les anciennes attributions
        $DB->delete_records('studentqcm_assignedqcm');
        $DB->execute("ALTER TABLE {studentqcm_assignedqcm} AUTO_INCREMENT = 1");

        // Récupérer tous les étudiants
        $students = $DB->get_records('user', null, '', 'id');
        $student_ids = array_keys($students);

        if (count($student_ids) < 2) {
            mtrace("Il faut au moins 2 étudiants pour faire l'attribution !");
            die();
        }

        shuffle($student_ids); // Mélanger la liste des étudiants

        // Dictionnaire pour compter les assignations
        $assignment_count = array_fill_keys($student_ids, 0);

        foreach ($student_ids as $student_id) {
            $assignments = [];

            // Liste des étudiants disponibles (pas soi-même et max 3 assignations)
            $possible_assignees = array_values(array_filter($student_ids, function ($id) use ($student_id, $assignment_count) {
                return $id !== $student_id && ($assignment_count[$id] ?? 0) < 3;
            }));

            shuffle($possible_assignees);

            // Déterminer combien on doit en assigner (2 ou 3)
            $num_assignees = min(rand(2, 3), count($possible_assignees));
            $assigned_students = array_slice($possible_assignees, 0, $num_assignees);

            // Stocker les attributions
            $record = new \stdClass();
            $record->user_id = $student_id;
            $record->prod1_id = $assigned_students[0] ?? null;
            $record->prod2_id = $assigned_students[1] ?? null;
            $record->prod3_id = $assigned_students[2] ?? null;

            $DB->insert_record('studentqcm_assignedqcm', $record);

            // Mettre à jour le compteur d'assignations
            foreach ($assigned_students as $assignee) {
                $assignment_count[$assignee] = ($assignment_count[$assignee] ?? 0) + 1;
                
                // Si un étudiant atteint 3 assignations, on le retire des choix futurs
                if ($assignment_count[$assignee] >= 3) {
                    unset($assignment_count[$assignee]);
                }
            }
        }
        // Marquer la tâche comme terminée
        $studentqcm->attribution_student_completed = 1;
        $DB->update_record('studentqcm', $studentqcm);
        mtrace("Attribution des productions entre étudiants terminée !");
    }
}
