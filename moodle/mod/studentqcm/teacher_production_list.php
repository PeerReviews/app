<?php

// Inclure le fichier de configuration de Moodle pour initialiser l'environnement Moodle
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours depuis l'URL
$id = required_param('id', PARAM_INT);

// Obtenir les informations du module de cours
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

// Vérifier que l'utilisateur est connecté
require_login($course, true, $cm);
$user_id = $USER->id;

// Récupérer les productions assignées à l'étudiant
$assigned_students = $DB->get_record('pr_assigned_student_teacher', array('teacherid' => $user_id), 'userid');


// Définir l'URL de la page et les informations de la page
$PAGE->set_url('/mod/studentqcm/teacher_production_list.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

// Charger les fichiers CSS nécessaires
$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

// Afficher l'en-tête de la page
echo $OUTPUT->header();

echo "<div class='mx-auto'>";
    echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('assigned_prod_list', 'mod_studentqcm') . "</p>";
echo "</div>";

// Bouton de retour
echo "<div class='flex mt-8 text-lg justify-start'>";
    echo "<a href='view.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
    echo "<i class='fas fa-arrow-left mr-2'></i>";
    echo get_string('back', 'mod_studentqcm');
    echo "</a>";
echo "</div>";

if ($assigned_students) {
    echo "<div class='space-y-4 mt-4'>";
    $nb = 1;
    foreach ($assigned_students as $student) {
        
        $prod_id = $student;
        echo "<div class='p-4 bg-white rounded-3xl shadow flex items-center justify-between'>";
            echo "<p class='font-semibold text-2xl text-gray-700 flex items-center gap-2'>";
            echo "<i class='fas fa-clipboard text-indigo-400 mr-2'></i>";
            echo "Production " . ($nb++);
            echo "</p>";

            // Bouton d'évaluation
            echo "<div>";
            echo "<a href='teacher_production_eval.php?id={$id}&prod_id={$prod_id}' class='px-4 py-2 bg-indigo-400 text-white text-lg font-semibold rounded-2xl hover:bg-indigo-500'>";
            echo "<i class='fas fa-pen-to-square mr-2'></i> " . get_string('evaluate', 'mod_studentqcm');
            echo "</a>";
            echo "</div>";
        echo "</div>";
        
    }
    
    echo "</div>";
} else {
    echo "<p class='text-center text-lg text-gray-600'>" . get_string('no_assigned_prod', 'mod_studentqcm') . "</p>";
}

echo $OUTPUT->footer();
