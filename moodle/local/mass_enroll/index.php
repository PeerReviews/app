<?php
require_once('../../config.php');
require_once('lib.php');  // Inclure les fonctions spécifiques du plugin
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/mass_enroll/index.php'));
$PAGE->set_title(get_string('mass_enroll', 'local_mass_enroll'));

echo $OUTPUT->header();
echo html_writer::tag('h2', get_string('mass_enroll', 'local_mass_enroll'));

// Sélection du cours
$courses = $DB->get_records_menu('course', null, 'fullname', 'id, fullname');
echo '<form enctype="multipart/form-data" method="post" action="massenrol.php">
        <label for="courseid">'.get_string('selectcourse', 'local_mass_enroll').'</label>
        <select name="id" id="courseid">';
foreach ($courses as $courseid => $coursename) {
    echo '<option value="'.$courseid.'">'.$coursename.'</option>';
}
echo '</select><br><br>';

echo '<label for="uploadcsv">'.get_string('uploadcsv', 'local_mass_enroll').'</label>
      <input type="file" name="csvfile" id="uploadcsv" /><br><br>
      <input type="submit" value="'.get_string('processcsv', 'local_mass_enroll').'" />
      </form>';

echo $OUTPUT->footer();
