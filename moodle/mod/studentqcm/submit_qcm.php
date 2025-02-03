<?php

// Inclure le fichier de configuration de Moodle pour initialiser l'environnement Moodle
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours depuis l'URL
$id = required_param('id', PARAM_INT);

// Obtenir les informations du module de cours
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

// Vérifier que l'utilisateur est connecté et qu'il a les droits nécessaires
require_login($course, true, $cm);

// Récupérer les données envoyées par le formulaire
$questions = required_param_array('questions', PARAM_RAW);

// Préparer le contenu du QCM (en XML)
$qcm_content = '<qcm>';
foreach ($questions as $q_id => $question) {
    // Vérifier que la question a un texte
    if (!empty($question['question'])) {
        $qcm_content .= '<question>';
        $qcm_content .= '<question_text>' . format_string($question['question']) . '</question_text>';
        $qcm_content .= '<answers>';
        
        foreach ($question['answers'] as $answer_id => $answer) {
            if (!empty($answer['answer'])) {
                $qcm_content .= '<answer>';
                $qcm_content .= '<answer_text>' . format_string($answer['answer']) . '</answer_text>';
                $qcm_content .= '<explanation>' . format_string($answer['explanation']) . '</explanation>';
                $qcm_content .= '<correct>' . (isset($answer['correct']) && $answer['correct'] == '1' ? 'true' : 'false') . '</correct>';
                $qcm_content .= '</answer>';
            }
        }

        $qcm_content .= '</answers>';
        $qcm_content .= '</question>';
    }
}
$qcm_content .= '</qcm>';

// Insérer le QCM dans la base de données
$record = new stdClass();
$record->userid = $USER->id;
$record->qcm_content = $qcm_content;
$record->timecreated = time();
$record->status = 'active'; // Ou 'reviewed' en fonction du statut que tu veux
$DB->insert_record('studentqcm_form', $record);

// Rediriger après l'insertion
redirect(new moodle_url('/mod/studentqcm/qcm_list.php', array('id' => $id)), get_string('qcm_saved', 'mod_studentqcm'), 2);
