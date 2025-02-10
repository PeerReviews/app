<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$prod_id = required_param('prod_id', PARAM_INT); // Récupération du prod_id dynamique

$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$userid = $USER->id;

// Charger les questions de la production assignée
$qcms = array();

// Vérifier si l'ID de la production assignée est valide
if (!empty($prod_id)) {
    // Charger les questions associées à cette production spécifique
    $questions = $DB->get_records('studentqcm_question', array('userid' => $prod_id));

    // Ajouter chaque question au tableau $qcms
    foreach ($questions as $question) {
        $qcms[] = $question;
    }
}


// Charger les noms des référentiels, compétences, sous-compétences et mots-clés
$referentiels = $DB->get_records_menu('referentiel', null, '', 'id, name');
$competencies = $DB->get_records_menu('competency', null, '', 'id, name');
$subcompetencies = $DB->get_records_menu('subcompetency', null, '', 'id, name');

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/eval_qcm_list.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
    echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('production1', 'mod_studentqcm') . "</p>";
echo "</div>";

// Boutons de navigation
echo "<div class='flex mt-8 text-lg justify-between'>";
    echo "<a href='eval_prod_list.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
    echo "<i class='fas fa-arrow-left mr-2'></i>";
    echo get_string('back', 'mod_studentqcm');
    echo "</a>";

echo "</div>";

if ($qcms) {
    echo "<div class='space-y-4 mt-4'>";
    
    foreach ($qcms as $qcm) {
        $nom_referentiel = isset($referentiels[$qcm->referentiel]) ? $referentiels[$qcm->referentiel] : get_string('unknown', 'mod_studentqcm');
        $nom_competency = isset($competencies[$qcm->competency]) ? $competencies[$qcm->competency] : get_string('unknown', 'mod_studentqcm');
        $nom_subcompetency = isset($subcompetencies[$qcm->subcompetency]) ? $subcompetencies[$qcm->subcompetency] : get_string('unknown', 'mod_studentqcm');

        echo "<div class='p-4 bg-white rounded-3xl shadow flex items-center justify-between'>";

            // Partie gauche (question + infos)
            echo "<div>";
            echo "<p class='font-semibold text-2xl text-gray-700 flex items-center gap-2 mb-4'>";
            echo format_string(ucfirst($qcm->question));
            echo "</p>";

            // Informations sur le référentiel, compétence et sous-compétence
            echo "<div class='mt-2 text-gray-600 text-sm flex flex-col space-y-1'>";

            // Référentiel
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-book text-green-500'></i>";
            echo "<span>" . get_string('referentiel', 'mod_studentqcm') . ": <strong>" . ucfirst($nom_referentiel) . "</strong></span>";
            echo "</p>";

            // Compétence
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-bookmark text-orange-500'></i>";
            echo "<span>" . get_string('competency', 'mod_studentqcm') . ": <strong>" . ucfirst($nom_competency) . "</strong></span>";
            echo "</p>";

            // Sous-compétence
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-award text-purple-500'></i>";
            echo "<span>" . get_string('subcompetency', 'mod_studentqcm') . ": <strong>" . ucfirst($nom_subcompetency) . "</strong></span>";
            echo "</p>";

            echo "</div>";
            echo "</div>";


            // Partie droite (boutons)
            echo "<div class='flex space-x-2'>";
                echo "<a href='qcm_view.php?id={$id}&qcm_id={$qcm->id}' class='px-3 py-2 bg-sky-400 text-white rounded-lg hover:bg-sky-500'>";
                echo "<i class='fas fa-square-check'></i>";
                echo "</a>";

            echo "</div>";


        echo "</div>";
    }
    
    echo "</div>";
} else {
    echo "<p class='text-center text-lg text-gray-600'>" . get_string('qcm_not_found', 'mod_studentqcm') . "</p>";
}

?>
