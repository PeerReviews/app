<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);
$session = $DB->get_record('studentqcm', ['id' => $session_id], '*', MUST_EXIST);
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_login();
$context = context_module::instance($cm->id);
$PAGE->set_context($context);
$PAGE->set_url('/mod/studentqcm/edit_session.php', ['id' => $id]);
$PAGE->set_title(format_string($session->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', ['v' => time()]));

echo $OUTPUT->header();

// Début du formulaire
echo '<form action="save_session.php?id=' . $id . '&session_id=' . $session->id . '" method="post" class="session-form">';
echo '<input type="hidden" name="session_id" value="' . $session->id . '">';

$fields = [
    'name' => 'Nom de la session',
    'intro' => 'Introduction',
    'referentiel' => 'Référentiel',
    'nbqcm' => 'Nombre de QCM',
    'nbqcu' => 'Nombre de QCU',
    'nbtcs' => 'Nombre de TCS',
    'nbpop' => 'Nombre de Pop',
    'introformat' => 'Format de l\'introduction'
];

foreach ($fields as $field => $label) {
    echo '<div class="form-group">';
    echo '<label for="' . $field . '">' . $label . ' : </label>';
    $type = in_array($field, ['referentiel', 'nbqcm', 'nbqcu', 'nbtcs', 'nbpop', 'introformat']) ? 'number' : 'text';
    echo '<input type="' . $type . '" id="' . $field . '" name="' . $field . '" value="' . htmlspecialchars($session->$field) . '" required>';
    echo '</div>';
}

// Champs de date
$date_fields = ['start_date_1', 'end_date_1', 'end_date_tt_1',
                'start_date_2', 'end_date_2', 'end_date_tt_2',
                'start_date_3', 'end_date_3', 'end_date_tt_3'];

foreach ($date_fields as $field) {
    echo '<div class="form-group">';
    echo '<label for="' . $field . '">' . ucfirst(str_replace('_', ' ', $field)) . ' : </label>';
    echo '<input type="datetime-local" id="' . $field . '" name="' . $field . '" value="' . 
         (!empty($session->$field) ? date('Y-m-d\TH:i', $session->$field) : '') . '">';
    echo '</div>';
}

echo '<button type="submit" class="btn-save">Sauvegarder</button>';
echo '</form>';

echo $OUTPUT->footer();
?>