<?php
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours
$id = required_param('id', PARAM_INT);
$prod_id = required_param('prod_id', PARAM_INT);

// Debug : Vérifier l'URL (optionnel)
echo $_SERVER['REQUEST_URI']; 

// Récupérer les informations du module et vérifier l'accès
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);
require_login($course, true, $cm);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $explanation = trim(required_param('evaluation_comment', PARAM_RAW));
    $question_id = required_param('question_id', PARAM_INT);
    $userid = $USER->id;

    if (!empty($explanation) && $question_id > 0) {
        // Vérifier si une évaluation existe déjà
        $existing_evaluation = $DB->get_record('studentqcm_evaluation', array(
            'question_id' => $question_id,
            'userid' => $userid
        ));

        if ($existing_evaluation) {
            // Mise à jour de l'évaluation existante
            $existing_evaluation->explanation = $explanation;
            $DB->update_record('studentqcm_evaluation', $existing_evaluation);
        } else {
            // Création d'une nouvelle évaluation
            $record = new stdClass();
            $record->question_id = $question_id;
            $record->explanation = $explanation;
            $record->userid = $userid;
            $record->status = 1;

            $DB->insert_record('studentqcm_evaluation', $record);
        }

        // Redirection avec message de succès
        redirect(new moodle_url('/mod/studentqcm/eval_qcm_list.php', array('id' => $id, 'prod_id' => $prod_id)));
    } else {
        // Erreur : champs vides ou ID invalide
        redirect(new moodle_url('/mod/studentqcm/eval_qcm_view.php', array('id' => $id, 'prod_id' => $prod_id, 'qcm_id' => $question_id)), 
                 "Erreur : le commentaire est vide ou l'ID de la question est invalide.", null, \core\output\notification::NOTIFY_ERROR);
    }
}
?>

