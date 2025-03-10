<?php

// Inclure le fichier de configuration de Moodle pour initialiser l'environnement Moodle
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours et l'ID du QCM depuis l'URL
$id = required_param('id', PARAM_INT);  // ID du cours
$session_id = required_param('session_id', PARAM_INT);  // ID du QCM à supprimer

// Obtenir les informations du module de cours
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);

// Vérifier que l'utilisateur est connecté et qu'il a les droits nécessaires
require_login($course, true, $cm);

// Supprimer la session de la base de données
$DB->delete_records('studentqcm', array('id' => $session_id));

// Rediriger vers la liste des QCM avec un message de succès
redirect(new moodle_url('/mod/studentqcm/admin_session_gestion.php', array('id' => $id)), get_string('qcm_deleted', 'mod_studentqcm'), null, \core\output\notification::NOTIFY_SUCCESS);

?>
