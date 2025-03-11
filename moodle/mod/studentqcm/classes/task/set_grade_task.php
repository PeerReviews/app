<?php
namespace mod_studentqcm\task;

use core\task\scheduled_task;

class set_grade_task extends scheduled_task {

    public function get_name() {
        return "Assignation des notes aux étudiants après correction des enseignants";
    }

    public function execute(bool $force = false) {
        global $DB;

        $studentqcm = $DB->get_record('studentqcm', ['archived' => 0]);

        if (!$studentqcm || empty($studentqcm->date_jury)) {
            mtrace("Erreur : date de jury introuvable.");
            die();
        }

        $current_timestamp = strtotime(time()); 
        $start_timestamp = strtotime($studentqcm->date_jury);

        if ($current_timestamp < $start_timestamp) {
            mtrace("La date de jury n'est pas encore atteinte.");
            die();
        }

        $students = $DB->get_records('students');

        $nbTotalQuestionPop = 0;
        $popTypes = $DB->get_records('question_pop', array('refId' => $studentqcm->id));
        foreach($popTypes as $popType){
            $nbTotalQuestionPop += $popType->nbqcm + $popType->nbqcu;
        }

        $nb_questions = $studentqcm->nbqcm + $studentqcm->nbqcu + $studentqcm->nbtcs + $nbTotalQuestionPop;
        

        foreach($students as $student){
            $questions = $DB->get_records('studentqcm_question', ['userid' => $student->userid]);
            $evaluations = $DB->get_records('studentqcm_evaluation', ['userid' => $student->userid]);

            $productions = $DB->get_record('studentqcm_assignedqcm', ['user_id' => $student->userid], 'prod1_id, prod2_id, prod3_id');
            $nb_revisions = 0;

            if ($productions) {
                foreach ((array) $productions as $production_id) {
                    if (!empty($production_id)) {
                        $to_evaluate = $DB->get_records('studentqcm_question', array('userid' => $production_id, 'status' => 1));
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
            $student_grade->production_grade = ($total_questions/$nb_questions)*20;
            $student_grade->revision_grade = ($total_revisions/$nb_revisions)*20;
            $student_grade->global_grade = ($total_questions + $total_revisions)/2;
            $DB->update_record('pr_grade', $student);
        }
    }
}