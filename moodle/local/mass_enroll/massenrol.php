<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

// Get params.
$id = optional_param('id', 0, PARAM_INT);

// Si un ID de cours est fourni, vérifier l'accès à ce cours.
if ($id > 0) {
    if (!$course = $DB->get_record('course', ['id' => $id])) {
        throw new \moodle_exception("Course is misconfigured");
    }
    
    require_course_login($course);
    $context = context_course::instance($course->id);
    require_capability('local/mass_enroll:enrol', $context);
} else {
    // Contexte global si aucun cours n'est spécifié.
    require_login();
    $context = context_system::instance();
}

$PAGE->set_context($context);
$PAGE->set_url('/mod/studentqcm/massenrol.php', array('id' => $id));

if ($id <= 0) {
    echo $OUTPUT->header();
    
    echo html_writer::tag('h1', get_string('choosecourse', 'local_mass_enroll'));

    $courses = get_courses();
    $options = [];
    foreach ($courses as $c) {
        $options[$c->id] = $c->fullname;
    }

    echo html_writer::start_tag('div', ['class' => 'd-flex justify-content-center mt-3']);
        echo html_writer::start_tag('div', [
            'class' => 'card p-4 shadow-sm border-0', 
            'style' => 'max-width: 400px; width: 100%; background-color: #f8f9fa; border-radius: 10px;'
        ]);

        echo html_writer::tag('h5', get_string('choosecourse', 'local_mass_enroll'), [
            'class' => 'text-center mb-3',
            'style' => 'color: #28a745; font-weight: bold;'
        ]);

        echo html_writer::start_tag('form', [
            'method' => 'get',
            'action' => new moodle_url('/local/mass_enroll/massenrol.php')
        ]);

        echo html_writer::start_tag('div', ['class' => 'mb-3']);
        echo html_writer::select(
            $options,
            'id',
            $id,
            ['' => get_string('choosecourse', 'local_mass_enroll')],
            [
                'class' => 'form-control',
                'style' => 'width: 100%; padding: 8px;'
            ]
        );
        echo html_writer::end_tag('div');

        echo html_writer::empty_tag('input', [
            'type' => 'submit',
            'value' => get_string('go'),
            'class' => 'btn btn-success btn-block',
            'style' => 'transition: background-color 0.3s; width: 100%;'
        ]);

        echo html_writer::end_tag('form');
        echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');

    echo $OUTPUT->footer();

} else {
    // Configuration de la page.
    $strinscriptions = get_string('mass_enroll', 'local_mass_enroll');
    $PAGE->set_pagelayout('incourse');
    $PAGE->set_url(new moodle_url($CFG->wwwroot . '/local/mass_enroll/massenrol.php', ['id' => $id]));
    $PAGE->set_title($course->fullname . ': ' . $strinscriptions);
    $PAGE->set_heading($course->fullname . ': ' . $strinscriptions);

    $course = $PAGE->course;
    $renderer = $PAGE->get_renderer('local_mass_enroll');

    $form = new \local_mass_enroll\local\forms\massenrol(new moodle_url($PAGE->url), [
        'course' => $course,
        'context' => $context,
    ]);
    $result = $form->process();

    if ($result) {
        \core\notification::success(get_string('process:massenrol:success', 'local_mass_enroll'));
        redirect(new moodle_url('/course/view.php', ['id' => $course->id]));
    } else {
        echo $renderer->header();
        echo $renderer->get_tabs($context, 'massenrol', ['id' => $course->id]);
        echo $renderer->heading_with_help($strinscriptions, 'mass_enroll', 'local_mass_enroll',
                    'icon', get_string('mass_enroll', 'local_mass_enroll'));
        echo $renderer->box(get_string('mass_enroll_info', 'local_mass_enroll'), 'center');
        echo $form->render();
        echo $renderer->footer();
    }

    $enrol_manual = enrol_get_plugin('manual');
    if (!$enrol_manual) {
        throw new moodle_exception('Enrolment plugin not found or not enabled.');
    }
}

