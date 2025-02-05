<?php

require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours et l'ID du QCM
$id = required_param('id', PARAM_INT);  // ID du module de cours
$qcm_id = required_param('qcm_id', PARAM_INT); // ID du QCM à modifier

// Obtenir les informations du module de cours
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

// Vérifier que l'utilisateur est connecté et qu'il a les droits nécessaires
require_login($course, true, $cm);

// Vérifier que l'utilisateur est bien le créateur du QCM
$qcm = $DB->get_record('studentqcm_question', array('id' => $qcm_id), '*', MUST_EXIST);
if ($qcm->userid != $USER->id) {
    print_error('unauthorized', 'mod_studentqcm');
}

// Récupérer les données du formulaire
$referentiel = required_param('referentiel', PARAM_INT);
$competency = required_param('competency', PARAM_INT);
$subcompetency = required_param('subcompetency', PARAM_INT);
$question = required_param('questions[1][question]', PARAM_TEXT);
$context = required_param('questions[1][context]', PARAM_TEXT);
$global_comment = required_param('questions[1][global_comment]', PARAM_TEXT);
$keywords = optional_param_array('questions[1][keywords]', [], PARAM_INT);

// Valider la question
if (empty(trim($question))) {
    print_error('question_empty', 'mod_studentqcm');
}

// Mise à jour de la question
$qcm_record = new stdClass();
$qcm_record->id = $qcm_id;
$qcm_record->question = clean_param($question, PARAM_TEXT);
$qcm_record->context = clean_param($context, PARAM_TEXT);
$qcm_record->global_comment = clean_param($global_comment, PARAM_TEXT);
$qcm_record->referentiel = $referentiel;
$qcm_record->competency = $competency;
$qcm_record->subcompetency = $subcompetency;
$qcm_record->userid = $USER->id;  // Utilisateur actuel

// Mettre à jour le QCM dans la base de données
if (!$DB->update_record('studentqcm_question', $qcm_record)) {
    print_error('update_failed', 'mod_studentqcm');
}

// Mettre à jour les mots-clés associés
// Supprimer les anciens mots-clés
$DB->delete_records('keyword_question', ['question_id' => $qcm_id]);

// Ajouter les nouveaux mots-clés
if (!empty($keywords)) {
    foreach ($keywords as $keyword_id) {
        $keyword_record = new stdClass();
        $keyword_record->question_id = $qcm_id;
        $keyword_record->keyword_id = $keyword_id;
        if (!$DB->insert_record('keyword_question', $keyword_record)) {
            print_error('insert_keyword_failed', 'mod_studentqcm');
        }
    }
}

// Mettre à jour les réponses
$answers = optional_param_array('answers', [], PARAM_RAW);
foreach ($answers as $index => $answer_data) {
    // Récupérer chaque réponse
    $answer_id = $answer_data['id'];
    $answer_text = clean_param($answer_data['answer'], PARAM_TEXT);
    $explanation = clean_param($answer_data['explanation'], PARAM_TEXT);
    $isTrue = isset($answer_data['correct']) && $answer_data['correct'] == '1' ? 1 : 0;

    // Mise à jour de la réponse
    $answer_record = new stdClass();
    $answer_record->id = $answer_id;
    $answer_record->question_id = $qcm_id;
    $answer_record->answer = $answer_text;
    $answer_record->explanation = $explanation;
    $answer_record->isTrue = $isTrue;

    // Mettre à jour la réponse dans la base de données
    if (!$DB->update_record('studentqcm_answer', $answer_record)) {
        print_error('update_answer_failed', 'mod_studentqcm');
    }
}

// Rediriger vers la page de la liste des QCM avec un message de succès
redirect(new moodle_url('/mod/studentqcm/qcm_list.php', array('id' => $id)), get_string('qcm_updated', 'mod_studentqcm'), 2);
