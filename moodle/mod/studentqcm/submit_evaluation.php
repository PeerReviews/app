<?php
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours
$id = required_param('id', PARAM_INT);
$prod_id = required_param('prod_id', PARAM_INT);

echo $_SERVER['REQUEST_URI'];  // Affiche l'URL actuelle pour vérifier


// Récupérer les informations du module et vérifier l'accès
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);
require_login($course, true, $cm);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $explanation = trim($_POST['evaluation_comment']);
    $question_id = intval($_POST['question_id']);

    if (!empty($explanation) && $question_id > 0) {
        $record = new stdClass();
        $record->question_id = $question_id;
        $record->explanation = $explanation;

        $DB->insert_record('studentqcm_evaluation', $record);
        echo "Évaluation enregistrée avec succès !";
    } else {
        echo "Erreur : le commentaire est vide ou l'ID de la question est invalide.";
    }
}

redirect(new moodle_url('/mod/studentqcm/eval_qcm_list.php', array('id' => $id, 'prod_id' => $prod_id)));

?>
