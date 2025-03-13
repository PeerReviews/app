<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);
$session = $DB->get_record('peerreview', ['id' => $session_id], '*', MUST_EXIST);

require_login();

$session->archived = 0;

$DB->set_field('peerreview', 'archived', 1);
$DB->update_record('peerreview', $session);

redirect(new moodle_url('/mod/peerreview/admin_sessions.php', ['id' => $id]));


?>
