<?php

// Inclure le fichier de configuration de Moodle pour initialiser l'environnement Moodle
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours et l'ID du QCM depuis l'URL
$id = required_param('id', PARAM_INT);  // ID du cours
$qcm_id = required_param('qcm_id', PARAM_INT);  // ID du QCM à supprimer

// Obtenir les informations du module de cours
$cm = get_coursemodule_from_id('peerreview', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$peerreview = $DB->get_record('peerreview', array('id' => $cm->instance), '*', MUST_EXIST);

$session = $DB->get_record('peerreview', ['archived' => 0], '*', MUST_EXIST);

// Vérifier que l'utilisateur est connecté et qu'il a les droits nécessaires
require_login($course, true, $cm);

// Vérifier si l'utilisateur est bien celui qui a créé le QCM
$qcm = $DB->get_record('pr_question', array('id' => $qcm_id), '*', MUST_EXIST);
if ($qcm->userid != $USER->id) {
    // L'utilisateur n'est pas autorisé à supprimer ce QCM
    redirect(new moodle_url('/mod/peerreview/qcm_list.php', array('id' => $id)), get_string('not_allowed', 'mod_peerreview'), null, \core\output\notification::NOTIFY_ERROR);
}

// Supprimer le QCM de la base de données
$DB->delete_records('pr_question', array('id' => $qcm_id));

// Rediriger vers la liste des QCM avec un message de succès
redirect(new moodle_url('/mod/peerreview/qcm_list.php', array('id' => $id)), get_string('qcm_deleted', 'mod_peerreview'), null, \core\output\notification::NOTIFY_SUCCESS);

?>
