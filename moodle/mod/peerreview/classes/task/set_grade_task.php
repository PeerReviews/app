<?php
namespace mod_peerreview\task;

use core\task\scheduled_task;

class set_grade_task extends scheduled_task {

    public function get_name() {
        return "Assignation des notes aux étudiants après correction des enseignants";
    }

    public function execute(bool $force = false) {
        global $DB;

        $session = $DB->get_record('peerreview', ['archived' => 0]);

        if (!$session || empty($session->date_jury)) {
            mtrace("Erreur : date de jury introuvable.");
            die();
        }

        $current_timestamp = strtotime(time()); 
        $start_timestamp = strtotime($session->date_jury);

        if ($current_timestamp < $start_timestamp) {
            mtrace("La date de jury n'est pas encore atteinte.");
            die();
        }

        $students = $DB->get_records('pr_students', ['sessionid' => $session->id]);

        $nbTotalQuestionPop = 0;
        $popTypes = $DB->get_records('pr_question_pop', array('sessionid' => $session->id));
        foreach($popTypes as $popType){
            $nbTotalQuestionPop += $popType->nbqcm + $popType->nbqcu;
        }

        $nb_questions = $session->nbqcm + $session->nbqcu + $session->nbtcs + $nbTotalQuestionPop;
        

        foreach($students as $student){
            $questions = $DB->get_records('pr_question', ['userid' => $student->userid, 'sessionid' => $session->id]);
            $evaluations = $DB->get_records('pr_evaluation', ['userid' => $student->userid]);

            $productions = $DB->get_record('pr_assignedqcm', ['user_id' => $student->userid, 'sessionid' => $session->id], 'prod1_id, prod2_id, prod3_id');
            $nb_revisions = 0;

            if ($productions) {
                foreach ((array) $productions as $production_id) {
                    if (!empty($production_id)) {
                        $to_evaluate = $DB->get_records('pr_question', array('userid' => $production_id, 'sessionid' => $session->id, 'status' => 1));
                        $nb_revisions += count($to_evaluate);
                    }
                }
            }

            $total_questions = 0;
            $total_revisions = 0;

            foreach($questions as $question){
                $total_questions += $question->grade;
            }

            foreach($evaluations as $evaluation){
                $total_revisions += $evaluation->grade;
            }

            // Normaliser les notes sur 20

            $student_grade = new \stdClass();
            $student_grade->userid = $student->userid;
            $student_grade->sessionid = $session->id;
            $student_grade->production_grade = ($total_questions/$nb_questions)*20;
            $student_grade->revision_grade = ($total_revisions/$nb_revisions)*20;
            $student_grade->global_grade = (0.75 * $total_questions + 0.25 * $total_revisions)/2;
            $DB->update_record('pr_grade', $student);
        }
    }
}