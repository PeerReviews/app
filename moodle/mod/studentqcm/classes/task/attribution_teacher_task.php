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

        if (!$studentqcm || empty($studentqcm->end_date_3)) {
            mtrace("Erreur : date de lancement introuvable.");
            die();
        }

        // Vérifie si la tâche a déjà été effectuée
        if ($studentqcm->attribution_teacher_completed == 1) {
            mtrace("La tâche a déjà été effectuée.");
            die();  // Si déjà effectuée, on arrête l'exécution
        }

        $start_timestamp = $studentqcm->end_date_3;
        $current_timestamp = time();

        if ($current_timestamp < $start_timestamp) {
            mtrace("La date de lancement n'est pas encore atteinte. Attente...");
            die();
        }

        // Exécution de l'attribution automatique
        mtrace("Déclenchement de l'attribution automatique !");

        // TODO : algorithme d'attribution productions aux enseignants

        // Marquer la tâche comme terminée
        $studentqcm->attribution_teacher_completed = 1;
        $DB->update_record('studentqcm', $studentqcm);
        mtrace("Attribution des productions terminée !");
    }
}
