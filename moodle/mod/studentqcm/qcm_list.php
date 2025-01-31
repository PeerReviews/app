<?php

// Inclure le fichier de configuration de Moodle pour initialiser l'environnement Moodle
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours depuis l'URL
$id = required_param('id', PARAM_INT);

// Obtenir les informations du module de cours
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$userid = $USER->id;
$qcms = $DB->get_records('studentqcm_form', array('userid' => $userid, 'status' => 'active'));

// Vérifier que l'utilisateur est connecté et qu'il a les droits nécessaires
require_login($course, true, $cm);

// Définir l'URL de la page et les informations de la page
$PAGE->set_url('/mod/studentqcm/qcm_list.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

// Charger les fichiers CSS nécessaires
$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

// Afficher l'en-tête de la page
echo $OUTPUT->header();

echo "<div class='mx-auto'>";
    echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('qcm_list', 'mod_studentqcm') . "</p>";
echo "</div>";

echo "<div class='flex mt-16 text-lg justify-between'>";
    echo "<a href='view.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
    echo "<i class='fas fa-arrow-left mr-2'></i>";
    echo get_string('back', 'mod_studentqcm');
    echo "</a>";

    echo "<a href='qcm_add.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-lime-300 hover:bg-lime-400 cursor-pointer text-lime-700 no-underline'>";
    echo "<i class='fas fa-plus mr-2'></i>";
    echo get_string('add_qcm', 'mod_studentqcm');
    echo "</a>";
echo "</div>";

echo "<div class='rounded-2xl bg-gray-50 p-2 mt-8'>";
    if ($qcms) {
        echo "<div class='mt-8'>";
        echo "<p class='font-bold text-2xl text-gray-600'>" . get_string('your_qcms', 'mod_studentqcm') . "</p>";
        echo "<ul class='list-disc pl-8'>";
        
        // Afficher chaque QCM
        foreach ($qcms as $qcm) {
            echo "<li class='mt-2'><a href='" . $qcm->qcm_content . "' class='text-lime-500 hover:underline'>" . format_string($qcm->qcm_content) . "</a></li>";
        }
        
        echo "</ul>";
        echo "</div>";
    } else {
        echo "<p class='text-center text-lg'>" . get_string('qcm_not_found', 'mod_studentqcm') . "</p>";
    }
echo "</div>";

echo $OUTPUT->footer();