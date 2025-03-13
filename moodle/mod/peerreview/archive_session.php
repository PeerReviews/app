<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);

require_login();

// Vérifier si la session existe
$session = $DB->get_record('peerreview', ['id' => $session_id], '*', MUST_EXIST);

// Marquer la session comme archivée
$DB->set_field('peerreview', 'archived', 1, ['id' => $session_id]);

// Redirection après l'archivage
redirect(new moodle_url('/mod/peerreview/admin_sessions.php', ['id' => $id]));
