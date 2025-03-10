<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);

require_login();

// Vérifier si la session existe
$session = $DB->get_record('studentqcm', ['id' => $session_id], '*', MUST_EXIST);

// Marquer la session comme archivée
$DB->set_field('studentqcm', 'archived', 1, ['id' => $session_id]);

// Redirection après l'archivage
redirect(new moodle_url('/mod/studentqcm/admin_sessions.php', ['id' => $id]));
