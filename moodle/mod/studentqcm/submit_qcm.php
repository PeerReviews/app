<?php

require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours
$id = required_param('id', PARAM_INT);
$type = required_param('type', PARAM_TEXT);

// Récupérer les informations du module et vérifier l'accès
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);
require_login($course, true, $cm);

// Vérifier que la requête est bien un POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $questions = $_POST['questions'];

    foreach ($questions as $q_id => $question) {
        // Vérifier que la question a bien un texte
        if (!empty(trim($question['question']))) {
            // Préparer l'enregistrement de la question
            $question_record = new stdClass();
            $question_record->userid = $USER->id;
            $question_record->question = clean_param($question['question'], PARAM_TEXT);
            $question_record->indexation = 1;
            $question_record->global_comment = clean_param($question['global_comment'], PARAM_TEXT);
            $question_record->context = clean_param($question['context'], PARAM_TEXT);
            $question_record->referentiel = clean_param($question['referentiel'], PARAM_INT);
            $question_record->competency = clean_param($question['competency'], PARAM_INT);
            $question_record->subcompetency = clean_param($question['subcompetency'], PARAM_INT);
            $question_record->type = $type;

            // Insérer la question et récupérer son ID
            $question_id = $DB->insert_record('studentqcm_question', $question_record);
            if (!$question_id) {
                throw new moodle_exception('insertfailed', 'studentqcm_question');
            }


            // Insérer les réponses associées
            if (!empty($question['answers'])) {
                foreach ($question['answers'] as $answer) {
                    if (!empty(trim($answer['answer']))) {
                        $answer_record = new stdClass();
                        $answer_record->question_id = $question_id;
                        $answer_record->answer = clean_param($answer['answer'], PARAM_TEXT);
                        $answer_record->explanation = !empty($answer['explanation']) ? clean_param($answer['explanation'], PARAM_TEXT) : null;
                        $answer_record->isTrue = isset($answer['correct']) && $answer['correct'] == '1' ? 1 : 0;

                        try {
                            $inserted_answer_id = $DB->insert_record('studentqcm_answer', $answer_record);
                            if (!$inserted_answer_id) {
                                throw new moodle_exception('Error inserting answer: ' . print_r($answer_record, true));
                            }
                        } catch (Exception $e) {
                            debugging("Error inserting answer: " . $e->getMessage());
                            throw $e;
                        }
                    }
                }
            }

            // Vérifier et insérer les mots-clés
            if (!empty($question['keywords']) && is_array($question['keywords'])) {
                foreach ($question['keywords'] as $keyword_id) {
                    if (!empty(trim($keyword_id))) {
                        // Vérifier si la relation existe déjà dans mdl_question_keywords
                        $existing_relation = $DB->get_record('question_keywords', array(
                            'question_id' => $question_id,
                            'keyword_id' => $keyword_id
                        ));
            
                        if (!$existing_relation) {
                            // Préparer les données pour l'insertion
                            $relation_record = new stdClass();
                            $relation_record->question_id = $question_id;
                            $relation_record->keyword_id = $keyword_id;
            
                            // Insérer la relation dans la table
                            try {
                                $DB->insert_record('question_keywords', $relation_record);
                            } catch (Exception $e) {
                                debugging("Erreur lors de l'insertion du mot-clé : " . $e->getMessage());
                            }
                        } else {
                            debugging("Relation déjà existante pour question_id=$question_id et keyword_id=$keyword_id");
                        }
                    }
                }
            }
            
        }
    }

    // Redirection après l'enregistrement
    // redirect(new moodle_url('/mod/studentqcm/qcm_list.php', array('id' => $id)), get_string('qcm_saved', 'mod_studentqcm'), 2);
}

// Si la requête n'est pas un POST, rediriger
redirect(new moodle_url('/mod/studentqcm/qcm_list.php', array('id' => $id)));
