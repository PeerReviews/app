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

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css'));

// Vérifie les rôles de l'utilisateur dans le contexte du cours
$user_roles = get_user_roles(context_course::instance($course->id), $USER->id, true);

// Variables pour vérifier si l'utilisateur est un étudiant ou un professeur
$is_student = false;
$is_teacher = false;

// Vérifie si l'utilisateur a le rôle "student"
foreach ($user_roles as $role) {
    if ($role->shortname == 'student') {
        $is_student = true;
    }
    // Vérifie si l'utilisateur a le rôle "teacher"
    if ($role->shortname == 'editingteacher' || $role->shortname == 'teacher') {
        $is_teacher = true;
    }
}

echo $OUTPUT->header();

echo "<div class='mx-auto grid grid-cols-3 gap-4'>";

if ($is_teacher) {
    // Affichage pour les professeurs
    echo "<div class='mt-4 p-4 bg-green-100 rounded-lg'>";
    echo "<p class='font-semibold'>Vous êtes un professeur. Vous pouvez gérer ce QCM.</p>";
    echo "<a href='#' class='text-blue-500'>Accéder à la gestion</a>";
    echo "</div>";

} else if ($is_student) {
    // Affichage pour les étudiants
    echo "<div class='p-4 bg-lime-200 rounded-lg'>";
        echo "<p class='font-bold text-center text-lg'>" . get_string('phase1', 'mod_studentqcm') . "</p>";
        echo "<p class='font-bold text-center text-lg'>" . get_string('phase1_title', 'mod_studentqcm') . "</p>";
        if (isset($studentqcm->start_date_1) && $studentqcm->start_date_1) {
            echo "<p class='text-center pt-2'>Début : " . date('d M Y', $studentqcm->start_date_1) . "</p>";
        } else {
            echo "<p class='text-center'>Date non définie pour la Phase 1</p>";
        }
    echo "</div>";
    
    echo "<div class='p-4 bg-sky-200 rounded-lg'>";
        echo "<p class='font-bold text-center text-lg'>" . get_string('phase2', 'mod_studentqcm') . "</p>";
        echo "<p class='font-bold text-center text-lg'>" . get_string('phase2_title', 'mod_studentqcm') . "</p>";
        if (isset($studentqcm->start_date_2) && $studentqcm->start_date_2) {
            echo "<p class='text-center pt-2'>Début : " . date('d M Y', $studentqcm->start_date_2) . "</p>";
        } else {
            echo "<p class='text-center'>Date non définie pour la Phase 2</p>";
        }
        // echo "<a href='#' class='text-blue-500'>Répondre au QCM</a>";
    echo "</div>";

    echo "<div class='p-4 bg-indigo-200 rounded-lg'>";
        echo "<p class='font-bold text-center text-lg'>" . get_string('phase3', 'mod_studentqcm') . "</p>";
        echo "<p class='font-bold text-center text-lg'>" . get_string('phase3_title', 'mod_studentqcm') . "</p>";
        if (isset($studentqcm->start_date_3) && $studentqcm->start_date_3) {
            echo "<p class='text-center pt-2'>Début : " . date('d M Y', $studentqcm->start_date_3) . "</p>";
        } else {
            echo "<p class='text-center'>Date non définie pour la Phase 3</p>";
        }
        // echo "<a href='#' class='text-blue-500'>Répondre au QCM</a>";
    echo "</div>";
} else {
    // Affichage si l'utilisateur n'a ni rôle étudiant ni rôle professeur
    echo "<p class='text-red-600'>Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>";
}

echo "</div>";

echo $OUTPUT->footer();
