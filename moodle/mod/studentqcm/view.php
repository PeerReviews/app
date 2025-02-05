<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/studentqcm/lib.php');

$id = required_param('id', PARAM_INT); // ID du module de cours.
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);


$PAGE->set_url('/mod/studentqcm/view.php', array('id' => $id));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

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
    echo "<div class='p-4 bg-lime-200 rounded-3xl'>";
        echo "<p class='font-semibold text-center text-lg text-lime-700'>" . get_string('phase1', 'mod_studentqcm') . "</p>";
        echo "<p class='font-bold text-center text-xl text-lime-700 pt-2'>" . get_string('phase1_title', 'mod_studentqcm') . "</p>";

        $now = time();
        $start_date_1 = isset($studentqcm->start_date_1) ? $studentqcm->start_date_1 : 0;
        $is_available = ($now >= $start_date_1);

        echo "<p class='text-center pt-4 italic text-lime-600'>" . get_string('phase_start', 'mod_studentqcm') . " : " . date('d M Y', $start_date_1) . "</p>";

        echo "<div class='flex justify-center mt-2'>";
        if($is_available) {
            echo "<a href='qcm_list.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-lime-300 hover:bg-lime-400 cursor-pointer text-lime-700 no-underline'>";
            echo get_string('phase_available', 'mod_studentqcm');
            echo "<i class='fas fa-arrow-right ml-4'></i>";
            echo "</a>";
        } else {
            echo "<a href='#' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 text-gray-400 cursor-not-allowed no-underline'>";
            echo "<i class='fas fa-ban mr-4'></i>";
            echo get_string('phase_unavailable', 'mod_studentqcm');
            echo "</a>";
        }
        echo "</div>";
    echo "</div>";

    
    echo "<div class='p-4 bg-sky-200 rounded-3xl'>";
        echo "<p class='font-semibold text-center text-lg text-sky-700'>" . get_string('phase2', 'mod_studentqcm') . "</p>";
        echo "<p class='font-bold text-center text-xl pt-2 text-sky-700'>" . get_string('phase2_title', 'mod_studentqcm') . "</p>";

        $now = time();
        $start_date_2 = isset($studentqcm->start_date_2) ? $studentqcm->start_date_2 : 0;
        $is_available = ($now >= $start_date_2);

        echo "<p class='text-center pt-4 italic text-sky-600'>" . get_string('phase_start', 'mod_studentqcm') . " : " . date('d M Y', $start_date_2) . "</p>";
        echo "<div class='flex justify-center mt-2'>";
        if($is_available) {
            echo "<a href='eval_prod_list.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-sky-300 hover:bg-sky-400 cursor-pointer text-sky-700 no-underline'>";
            echo get_string('phase_available', 'mod_studentqcm');
            echo "<i class='fas fa-arrow-right ml-4'></i>";
            echo "</a>";
        } else {
            echo "<a href='#' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 text-gray-400 cursor-not-allowed no-underline'>";
            echo "<i class='fas fa-ban mr-4'></i>";
            echo get_string('phase_unavailable', 'mod_studentqcm');
            echo "</a>";
        }
        echo "</div>";
    echo "</div>";

    echo "<div class='p-4 bg-indigo-200 rounded-3xl'>";
        echo "<p class='font-semibold text-center text-lg text-indigo-700'>" . get_string('phase3', 'mod_studentqcm') . "</p>";
        echo "<p class='font-bold text-center text-xl text-indigo-700 pt-2'>" . get_string('phase3_title', 'mod_studentqcm') . "</p>";

        $now = time();
        $start_date_3 = isset($studentqcm->start_date_3) ? $studentqcm->start_date_3 : 0;
        $is_available = ($now >= $start_date_3);

        echo "<p class='text-center pt-4 text-indigo-600 italic'>" . get_string('phase_start', 'mod_studentqcm') . " : " . date('d M Y', $start_date_3) . "</p>";
        echo "<div class='flex justify-center mt-2'>";
        if($is_available) {
            echo "<a href='#' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-indigo-300 hover:bg-indigo-400 cursor-pointer text-indigo-700 no-underline'>";
            echo get_string('phase_available', 'mod_studentqcm');
            echo "<i class='fas fa-arrow-right ml-4'></i>";
            echo "</a>";
        } else {
            echo "<a href='#' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 text-gray-400 cursor-not-allowed no-underline'>";
            echo "<i class='fas fa-ban mr-4'></i>";
            echo get_string('phase_unavailable', 'mod_studentqcm');
            echo "</a>";
        }
        echo "</div>";
    echo "</div>";
} else {
    // Affichage si l'utilisateur n'a ni rôle étudiant ni rôle professeur
    echo "<p class='text-red-600'>Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>";
}

echo "</div>";

echo $OUTPUT->footer();
