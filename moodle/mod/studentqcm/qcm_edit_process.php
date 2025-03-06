<?php

require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours
$id = required_param('id', PARAM_INT);
$type = required_param('type', PARAM_TEXT);
$qcm_id = required_param('qcm_id', PARAM_INT);
$is_improved = required_param('is_improved', PARAM_INT);

// Récupérer les informations du module et vérifier l'accès
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);
require_login($course, true, $cm);

// Vérifier que la requête est bien un POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $questions = $_POST['questions'];

    $json_data = json_encode($questions);

    foreach ($questions as $q_id => $question) {
        if (!empty(trim($question['question']))) {
            $q_id = $qcm_id;

            // Vérifier si la question existe déjà dans la base de données
            $existing_question = $DB->get_record('studentqcm_question', ['id' => $q_id]);
            if (!$existing_question) {
                throw new moodle_exception('invalidquestion', 'mod_studentqcm');
            }

            $question_record = new stdClass();
            $question_record->userid = $USER->id;
            $question_record->question = clean_param($question['question'], PARAM_TEXT);
            $question_record->global_comment = clean_param($question['global_comment'], PARAM_TEXT);
            $question_record->context = clean_param($question['context'], PARAM_TEXT);
            $question_record->referentiel = isset($question['referentiel']) ? clean_param($question['referentiel'], PARAM_INT) : null;
            $question_record->competency = isset($question['competency']) ? clean_param($question['competency'], PARAM_INT) : null;
            $question_record->subcompetency = isset($question['subcompetency']) ? clean_param($question['subcompetency'], PARAM_INT) : null;
            $question_record->type = $type;
            $question_record->is_improved = $is_improved;
            if ($is_improved == 0){
                $question_record->status = isset($_POST['submit']) ? 1 : 0;
            }
            else{
                $question_record->status = 1;
            }

            $question_record->id = $q_id;
            $DB->update_record('studentqcm_question', $question_record);


            $indexation = 1;
            if (!empty($question['answers'])) {
                foreach ($question['answers'] as $i => $answer) {
                    
                    // Vérifier si une réponse avec le même indexation existe déjà
                    $existing_answer = $DB->get_record('studentqcm_answer', [
                        'question_id' => $q_id,
                        'indexation' => $indexation
                    ]);

                    $answer_record = new stdClass();
                    $answer_record->question_id = $q_id;
                    $answer_record->indexation = $indexation++;
                    $answer_record->answer = !empty($answer['answer']) ? clean_param($answer['answer'], PARAM_TEXT) : null;
                    $answer_record->explanation = !empty($answer['explanation']) ? clean_param($answer['explanation'], PARAM_TEXT) : null;
                    $answer_record->isTrue = (isset($answer['correct']) && in_array($answer['correct'], ['1', 1])) ? 1 : 0;

                    if ($existing_answer) {
                        // Mise à jour de la réponse existante
                        $answer_record->id = $existing_answer->id;
                        $DB->update_record('studentqcm_answer', $answer_record);
                    } else {
                        // Insertion d'une nouvelle réponse
                        $DB->insert_record('studentqcm_answer', $answer_record);
                    }
                    
                }
            }

            // Gestion des mots-clés
            if (!empty($question['keywords']) && is_array($question['keywords'])) {
                // Récupérer les mots-clés existants pour la question
                $existing_keywords = $DB->get_records('question_keywords', ['question_id' => $q_id]);
                $existing_keyword_ids = array_map(function($keyword) {
                    return $keyword->keyword_id;
                }, $existing_keywords);
            
                // Nouveaux mots-clés envoyés par l'utilisateur
                $new_keywords = $question['keywords'];
            
                // Mots-clés à ajouter (ceux qui ne sont pas déjà présents)
                $keywords_to_add = array_diff($new_keywords, $existing_keyword_ids);
            
                // Mots-clés à supprimer (ceux qui ne sont plus dans la liste envoyée)
                $keywords_to_remove = array_diff($existing_keyword_ids, $new_keywords);
            
                // Ajouter les nouveaux mots-clés
                foreach ($keywords_to_add as $keyword_id) {
                    if (!empty(trim($keyword_id))) {
                        $existing_keyword = $DB->get_record('keyword', ['id' => $keyword_id]);
                        if ($existing_keyword) {
                            $relation_record = new stdClass();
                            $relation_record->question_id = $q_id;
                            $relation_record->keyword_id = $keyword_id;
                            $DB->insert_record('question_keywords', $relation_record);
                        }
                    }
                }
            
                // Supprimer les mots-clés obsolètes
                foreach ($keywords_to_remove as $keyword_id) {
                    // Supprimer uniquement les mots-clés qui sont dans la base de données
                    $DB->delete_records('question_keywords', ['question_id' => $q_id, 'keyword_id' => $keyword_id]);
                }
            }


        }
    }

    // Redirection après enregistrement
    if ($is_improved == 1) {
        redirect(new moodle_url('/mod/studentqcm/phase3_qcm_list.php', array('id' => $id)), get_string('qcm_updated', 'mod_studentqcm'), 10);
    } else {
        redirect(new moodle_url('/mod/studentqcm/qcm_list.php', array('id' => $id)), get_string('qcm_updated', 'mod_studentqcm'), 10);
    }

}

// Redirection si la requête n'est pas un POST
if ($is_improved == 1) {
    redirect(new moodle_url('/mod/studentqcm/phase3_qcm_list.php', array('id' => $id)), get_string('qcm_updated', 'mod_studentqcm'), 10);
} else {
    redirect(new moodle_url('/mod/studentqcm/qcm_list.php', array('id' => $id)), get_string('qcm_updated', 'mod_studentqcm'), 10);
}

?>
