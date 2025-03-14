<?php
namespace mod_peerreview\task;

use core\task\scheduled_task;

class attribution_teacher_task extends scheduled_task {

    public function get_name() {
        return "Attribution automatique des productions aux étudiants";
    }

    public function execute(bool $force = false) {
        global $DB;

        $session = $DB->get_record('peerreview', ['archived' => 0]);

        if (!$session || empty($session->end_date_tt_3)) {
            mtrace("Erreur : date de lancement introuvable.");
            die();
        }

        // Vérifie si la tâche a déjà été effectuée
        if (!$force && $session->attribution_teacher_completed == 1) {
            mtrace("L'attribution automatique aux professeurs a déjà été effectuée.");
            die();  // Si déjà effectuée, on arrête l'exécution
        }

        $current_timestamp = strtotime(time()); 
        $start_timestamp = strtotime($session->end_date_tt_3);

        if ($current_timestamp < $start_timestamp) {
            mtrace("La date de lancement n'est pas encore atteinte.");
            die();
        }

        // Exécution de l'attribution automatique

        // Supprimer les anciennes attributions
        $DB->execute("TRUNCATE TABLE {pr_assigned_student_teacher}");

        // Récupération des enseignants
        $teachers = $DB->get_records('teachers', ['sessionid' => $session->id]);
        if (empty($teachers)) {
            mtrace("Erreur : aucun enseignant trouvé.");
            die();
        }

        // Récupération des étudiants
        $students = $DB->get_records('students', ['sessionid' => $session->id]);
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
                'studentid' => $student->id,
                'teacherid' => $teacher_id
            ];
            $index++;
        }

        // Insérer les attributions dans la table `pr_assigned_student_teacher`
        foreach ($assignments as $assignment) {
            $record = new \stdClass();
            if ($assignment['studentid'] == 0) {
                mtrace("Erreur : id étudiant invalide : ", print_r($assignment,true));
            }
            $record->userid = $assignment['studentid'];
            $record->teacherid = $assignment['teacherid'];
            $record->sessionid = $session->id;

            $DB->insert_record('pr_assigned_student_teacher', $record);
        }

        // Marquer la tâche comme terminée
        $session->attribution_teacher_completed = 1;
        $DB->update_record('peerreview', $session);
        mtrace("Attribution des productions aux enseignants terminée !");
    }
}
