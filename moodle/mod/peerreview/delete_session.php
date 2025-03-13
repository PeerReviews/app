<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);
$session = $DB->get_record('peerreview', ['id' => $session_id], '*', MUST_EXIST);

require_login();

$DB->delete_records('peerreview', ['id' => $session_id]);

redirect(new moodle_url('/mod/peerreview/admin_sessions.php', ['id' => $id]));
?>
