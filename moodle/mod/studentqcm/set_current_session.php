<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);
$session = $DB->get_record('studentqcm', ['id' => $session_id], '*', MUST_EXIST);

require_login();

$session->archived = 0;

$DB->set_field('studentqcm', 'archived', 1);
$DB->update_record('studentqcm', $session);

redirect(new moodle_url('/mod/studentqcm/admin_sessions.php', ['id' => $id]));


?>
