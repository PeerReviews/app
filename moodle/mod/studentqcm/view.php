<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/studentqcm/lib.php');

$id = required_param('id', PARAM_INT); // ID du module de cours.
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/view.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();
echo $OUTPUT->footer();