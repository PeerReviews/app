<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$prod_id = required_param('prod_id', PARAM_INT); // Récupération du prod_id dynamique

$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$session = $DB->get_record('studentqcm', ['archived' => 0], '*', MUST_EXIST);

$userid = $USER->id;

// Charger les questions de la production assignée
$questions = array();

// Vérifier si l'ID de la production assignée est valide
if (!empty($prod_id)) {
    // Charger les questions associées à cette production spécifique
    $qs = $DB->get_records('studentqcm_question', array('userid' => $prod_id, 'sessionid' => $session->id, 'status' => 1));

    // Ajouter chaque question au tableau $qcms
    foreach ($qs as $q) {
        $questions[] = $q;
    }
}


// Charger les noms des référentiels, compétences, sous-compétences et mots-clés
$referentiels = $DB->get_records_menu('referentiel', ['sessionid' => $session->id], '', 'id, name');
$competencies = $DB->get_records_menu('competency', ['sessionid' => $session->id], '', 'id, name');
$subcompetencies = $DB->get_records_menu('subcompetency', ['sessionid' => $session->id], '', 'id, name');

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

if ($questions) {
    echo "<div class='space-y-4 mt-4'>";

    // Trier les questions par popTypeId
    usort($questions, function($a, $b) {
        return $a->poptypeid <=> $b->poptypeid;
    });

    $previousPopTypeId = null;
    
    foreach ($questions as $question) {
        $nom_referentiel = isset($referentiels[$question->referentiel]) ? $referentiels[$question->referentiel] : get_string('unknown', 'mod_studentqcm');
        $nom_competency = isset($competencies[$question->competency]) ? $competencies[$question->competency] : get_string('unknown', 'mod_studentqcm');
        $nom_subcompetency = isset($subcompetencies[$question->subcompetency]) ? $subcompetencies[$question->subcompetency] : get_string('unknown', 'mod_studentqcm');

        // Vérifier si cette question a déjà été évalué par l'utilisateur
        $evaluation = $DB->get_record('studentqcm_evaluation', array('question_id' => $question->id, 'userid' => $USER->id));

        if ($evaluation && $evaluation->status == 1) {
            // Si la question a été évalué
            $buttonText = get_string('evaluated', 'mod_studentqcm');
            $buttonIcon = 'fas fa-check-circle';
            $buttonColor = 'bg-lime-400 hover:bg-lime-500';
            $buttonTextColor = 'text-white';
        } else {
            // Si la question n'a pas été évalué
            $buttonText = get_string('to_be_evaluated', 'mod_studentqcm');
            $buttonIcon = 'fas fa-pen-to-square';
            $buttonColor = 'bg-indigo-400 hover:bg-indigo-500';
            $buttonTextColor = 'text-white';
        }

        if ($question->poptypeid !== $previousPopTypeId) {
            $popInfo = $DB->get_record('question_pop', array('id' => $question->poptypeid, 'sessionid' => $session->id));
    
            if ($popInfo) {
                echo "<h2 class='text-xl font-semibold text-gray-700 text-center my-4 bg-gray-100 p-2 rounded-lg'>";
                echo "POP - {$popInfo->nbqcm} QCM, {$popInfo->nbqcu} QCU";
                echo "</h2>";
            } else {
                echo "<h2 class='text-xl font-semibold text-gray-700 text-center my-4 bg-gray-100 p-2 rounded-lg'>";
                echo "POP - " . get_string(ucfirst(unavailable_information));
                echo "</h2>";
            }
    
            $previousPopTypeId = $question->poptypeid;
        }

        echo "<div class='bg-white rounded-3xl shadow flex items-center justify-between'>";

            // Partie gauche (question + infos)
            echo "<div class='flex items-stretch gap-x-4'>";

            // Définir les couleurs en fonction du type de question
            if ($question->ispop == 1) {
                $bgColor = 'bg-yellow-200';
                $textColor = 'text-yellow-400';
            } else {
                switch ($question->type) {
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
                    case 'POP':
                        $bgColor = 'bg-green-200';
                        $textColor = 'text-green-700';
                        break;
                    default:
                        $bgColor = 'bg-indigo-200';
                        $textColor = 'text-indigo-400';
                }
            }

            $displayType = ($question->ispop == 1) ? "POP - " . ucfirst($question->type) : ucfirst($question->type);

            // Type de question
            echo "<div class='{$bgColor} rounded-l-3xl py-4 px-2 flex items-center w-16 justify-center'>"; 
            echo "<p class='font-semibold text-2xl {$textColor} flex items-center gap-2 -rotate-90 whitespace-nowrap'>";
            echo format_string($displayType);
            echo "</p>";
            echo "</div>";


            // Div contenant la question et les infos
            echo "<div class='flex flex-col justify-between p-4'>";

            echo "<p class='font-semibold text-2xl text-gray-700 flex items-center gap-2 mb-2'>";
            echo format_string(ucfirst($question->question));
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
            echo "<a href='eval_qcm_view.php?id={$id}&prod_id={$prod_id}&qcm_id={$question->id}' class='px-4 py-2 min-w-40 {$buttonColor} {$buttonTextColor} text-lg font-semibold rounded-2xl hover:{$buttonColor}'>";
            echo "<i class='{$buttonIcon} mr-2'></i> {$buttonText}";
            echo "</a>";
            echo "</div>";
        echo "</div>";
    }
    
    echo "</div>";
} else {
    echo "<p class='text-center text-lg text-gray-600'>" . get_string('not_found', 'mod_studentqcm') . "</p>";
}

echo $OUTPUT->footer();
?>
