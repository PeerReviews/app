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

// Récupérer toutes les questions créées par l'utilisateur
$qcms = $DB->get_records('studentqcm_question', array('userid' => $userid), 'id DESC');

// Charger les noms des référentiels, compétences, sous-compétences et mots-clés
$referentiels = $DB->get_records_menu('referentiel', null, '', 'id, name');
$competencies = $DB->get_records_menu('competency', null, '', 'id, name');
$subcompetencies = $DB->get_records_menu('subcompetency', null, '', 'id, name');

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

// Boutons de navigation
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

if ($qcms) {
    echo "<div class='space-y-4 mt-4'>";
    
    foreach ($qcms as $qcm) {
        // Récupérer les noms correspondants aux IDs
        $nom_referentiel = isset($referentiels[$qcm->referentiel]) ? $referentiels[$qcm->referentiel] : get_string('unknown', 'mod_studentqcm');
        $nom_competency = isset($competencies[$qcm->competency]) ? $competencies[$qcm->competency] : get_string('unknown', 'mod_studentqcm');
        $nom_subcompetency = isset($subcompetencies[$qcm->subcompetency]) ? $subcompetencies[$qcm->subcompetency] : get_string('unknown', 'mod_studentqcm');

        echo "<div class='p-4 bg-white rounded-lg shadow flex items-center justify-between'>";

            // Partie gauche (question + infos)
            echo "<div>";
                // Titre de la question
                echo "<p class='font-semibold text-xl text-gray-700'>" . format_string($qcm->question) . "</p>";

                // Informations sur le référentiel, compétence et sous-compétence (Affichage des NOMS et non des IDs)
                echo "<p class='text-gray-600 text-sm'>" . 
                     get_string('referentiel', 'mod_studentqcm') . ": <strong>{$nom_referentiel}</strong> | " . 
                     get_string('competency', 'mod_studentqcm') . ": <strong>{$nom_competency}</strong> | " . 
                     get_string('subcompetency', 'mod_studentqcm') . ": <strong>{$nom_subcompetency}</strong></p>";
            echo "</div>";

            // Partie droite (boutons)
            echo "<div class='flex space-x-2'>";
                echo "<a href='qcm_edit.php?id={$qcm->id}' class='px-3 py-1 bg-blue-500 text-white rounded-lg hover:bg-blue-600'>" . get_string('edit', 'mod_studentqcm') . "</a>";
                echo "<a href='qcm_delete.php?id={$qcm->id}' class='px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600' onclick='return confirm(\"" . get_string('confirm_delete', 'mod_studentqcm') . "\")'>" . get_string('delete', 'mod_studentqcm') . "</a>";
            echo "</div>";

        echo "</div>";
    }
    
    echo "</div>";
} else {
    echo "<p class='text-center text-lg text-gray-600'>" . get_string('qcm_not_found', 'mod_studentqcm') . "</p>";
}

echo $OUTPUT->footer();
