<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$prod_id = required_param('prod_id', PARAM_INT); // Récupération du prod_id dynamique

$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$userid = $USER->id;

// Vérifier si l'ID de la production assignée est valide
if (!empty($prod_id)) {
    // Charger les questions associées à cette production spécifique
    $qcms = $DB->get_records('studentqcm_question', array('userid' => $prod_id, 'ispop' => 0));
}

$pops = $DB->get_records('studentqcm_question', array('userid' => $prod_id, 'ispop' => 1));


// Charger les noms des référentiels, compétences, sous-compétences et mots-clés
$referentiels = $DB->get_records_menu('referentiel', null, '', 'id, name');
$competencies = $DB->get_records_menu('competency', null, '', 'id, name');
$subcompetencies = $DB->get_records_menu('subcompetency', null, '', 'id, name');

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/eval_qcm_list.php', array('id' => $id, 'prod_id' => $prod_id));
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

if ($qcms || $pops) {

    if ($qcms){
        echo "<div class='space-y-4 mt-4'>";
        
        foreach ($qcms as $qcm) {
            $nom_referentiel = isset($referentiels[$qcm->referentiel]) ? $referentiels[$qcm->referentiel] : get_string('unknown', 'mod_studentqcm');
            $nom_competency = isset($competencies[$qcm->competency]) ? $competencies[$qcm->competency] : get_string('unknown', 'mod_studentqcm');
            $nom_subcompetency = isset($subcompetencies[$qcm->subcompetency]) ? $subcompetencies[$qcm->subcompetency] : get_string('unknown', 'mod_studentqcm');

            // Vérifier si ce QCM a déjà été évalué par l'utilisateur
            $evaluation = $DB->get_record('studentqcm_evaluation', array('question_id' => $qcm->id, 'userid' => $USER->id));

            if ($evaluation && $evaluation->status == 1) {
                // Si le QCM a été évalué
                $buttonText = get_string('evaluated', 'mod_studentqcm');
                $buttonIcon = 'fa-regular fa-square-check';
                $buttonColor = 'bg-lime-400 hover:bg-lime-500';
                $buttonTextColor = 'text-white';
            } else {
                // Si le QCM n'a pas été évalué
                $buttonText = get_string('to_be_evaluated', 'mod_studentqcm');
                $buttonIcon = 'fas fa-pen-to-square';
                $buttonColor = 'bg-indigo-400 hover:bg-indigo-500';
                $buttonTextColor = 'text-white';
            }

            echo "<div class='bg-white rounded-3xl shadow flex items-center justify-between'>";

                // Partie gauche (question + infos)
                echo "<div class='flex items-stretch gap-x-4'>";

                // Définir les couleurs en fonction du type de question
                switch ($qcm->type) {
                    case 'QCM':
                        $bgColor = 'bg-indigo-200';
                        $textColor = 'text-indigo-400';
                        break;
                    case 'QCU':
                        $bgColor = 'bg-lime-200';
                        $textColor = 'text-lime-400';
                        break;
                    case 'TCS':
                        $bgColor = 'bg-sky-200';
                        $textColor = 'text-sky-400';
                        break;
                    default:
                        $bgColor = 'bg-indigo-200';
                        $textColor = 'text-indigo-400';
                }

                // Type de question
                echo "<div class='{$bgColor} rounded-l-3xl py-4 px-2 flex items-center w-16 justify-center'>"; 
                echo "<p class='font-semibold text-2xl {$textColor} flex items-center gap-2 -rotate-90 text-center'>";
                echo format_string(ucfirst($qcm->type));
                echo "</p>";
                echo "</div>";


                // Div contenant la question et les infos
                echo "<div class='flex flex-col justify-between p-4'>";

                echo "<p class='font-semibold text-2xl text-gray-700 flex items-center gap-2 mb-2'>";
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

                echo "</div>"; // Fin de div contenant la question et les infos
                echo "</div>"; // Fin de flex container

                echo "</div>"; // Fin de la partie gauche

                echo "<div class='flex space-x-2 p-4'>";
                echo "<a href='eval_qcm_view.php?id={$id}&prod_id={$prod_id}&qcm_id={$qcm->id}' class='px-4 py-2 min-w-40 {$buttonColor} {$buttonTextColor} text-lg font-semibold rounded-2xl hover:{$buttonColor}'>";
                echo "<i class='{$buttonIcon} mr-2'></i> {$buttonText}";
                echo "</a>";
                echo "</div>";
            echo "</div>";
        }
        
        echo "</div>";
    }

    if ($pops){
        echo "<div class='space-y-4 mt-4'>";

        // $pop = 
        
        foreach ($pop as $qcm) {
            $nom_referentiel = isset($referentiels[$qcm->referentiel]) ? $referentiels[$qcm->referentiel] : get_string('unknown', 'mod_studentqcm');
            $nom_competency = isset($competencies[$qcm->competency]) ? $competencies[$qcm->competency] : get_string('unknown', 'mod_studentqcm');
            $nom_subcompetency = isset($subcompetencies[$qcm->subcompetency]) ? $subcompetencies[$qcm->subcompetency] : get_string('unknown', 'mod_studentqcm');

            // Vérifier si ce QCM a déjà été évalué par l'utilisateur
            $evaluation = $DB->get_record('studentqcm_evaluation', array('question_id' => $qcm->id, 'userid' => $USER->id));

            if ($evaluation && $evaluation->status == 1) {
                // Si le QCM a été évalué
                $buttonText = get_string('evaluated', 'mod_studentqcm');
                $buttonIcon = 'fa-regular fa-square-check';
                $buttonColor = 'bg-lime-400 hover:bg-lime-500';
                $buttonTextColor = 'text-white';
            } else {
                // Si le QCM n'a pas été évalué
                $buttonText = get_string('to_be_evaluated', 'mod_studentqcm');
                $buttonIcon = 'fas fa-pen-to-square';
                $buttonColor = 'bg-indigo-400 hover:bg-indigo-500';
                $buttonTextColor = 'text-white';
            }

            if ($qcm->ispop){
                echo "<p> <strong> POP </strong> {$qcm->id} </p>";
            }

            echo "<div class='bg-white rounded-3xl shadow flex items-center justify-between'>";

                // Partie gauche (question + infos)
                echo "<div class='flex items-stretch gap-x-4'>";

                // Définir les couleurs en fonction du type de question
                switch ($qcm->type) {
                    case 'QCM':
                        $bgColor = 'bg-indigo-200';
                        $textColor = 'text-indigo-400';
                        break;
                    case 'QCU':
                        $bgColor = 'bg-lime-200';
                        $textColor = 'text-lime-400';
                        break;
                    default:
                        $bgColor = 'bg-indigo-200';
                        $textColor = 'text-indigo-400';
                }

                // Type de question
                echo "<div class='{$bgColor} rounded-l-3xl py-4 px-2 flex items-center w-16 justify-center'>"; 
                echo "<p class='font-semibold text-2xl {$textColor} flex items-center gap-2 -rotate-90 text-center'>";
                echo format_string(ucfirst($qcm->type));
                echo "</p>";
                echo "</div>";


                // Div contenant la question et les infos
                echo "<div class='flex flex-col justify-between p-4'>";

                echo "<p class='font-semibold text-2xl text-gray-700 flex items-center gap-2 mb-2'>";
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

                echo "</div>"; // Fin de div contenant la question et les infos
                echo "</div>"; // Fin de flex container

                echo "</div>"; // Fin de la partie gauche

                echo "<div class='flex space-x-2 p-4'>";
                echo "<a href='eval_qcm_view.php?id={$id}&prod_id={$prod_id}&qcm_id={$qcm->id}' class='px-4 py-2 min-w-40 {$buttonColor} {$buttonTextColor} text-lg font-semibold rounded-2xl hover:{$buttonColor}'>";
                echo "<i class='{$buttonIcon} mr-2'></i> {$buttonText}";
                echo "</a>";
                echo "</div>";
            echo "</div>";
        }
        
        echo "</div>";
    }
} 
else {
    echo "<p class='text-center text-lg text-gray-600'>" . get_string('qcm_not_found', 'mod_studentqcm') . "</p>";
}

echo $OUTPUT->footer();
?>
