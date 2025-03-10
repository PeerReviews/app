<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session = $DB->get_record('studentqcm', ['id' => $id], '*', MUST_EXIST);

require_login();

$PAGE->set_url('/mod/studentqcm/edit_session.php', ['id' => $id]);
$PAGE->set_title('Modifier la session ' . $session->name);
$PAGE->set_heading('Modifier la session');
$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

echo $OUTPUT->header();

// Formulaire de modification
echo '<form action="save_session.php" method="post">';
echo '<label for="name">Nom de la session :</label>';
echo '<input type="text" id="name" name="name" value="' . $session->name . '" required>';
echo '<input type="hidden" name="session_id" value="' . $session->id . '">';
echo '<button type="submit">Sauvegarder</button>';
echo '</form>';

echo $OUTPUT->footer();
?>
