<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$prod_id = required_param('prod_id', PARAM_INT);

$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$userid = $USER->id;

// Charger les noms des référentiels, compétences, sous-compétences et mots-clés
$referentiels = $DB->get_records_menu('referentiel', null, '', 'id, name');
$competencies = $DB->get_records_menu('competency', null, '', 'id, name');
$subcompetencies = $DB->get_records_menu('subcompetency', null, '', 'id, name');

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

$nb_eval_questions = count(array_filter($qcms, function($q) {
    return $q->grade !== null;
}));

$nb_evaluated_revisions = 0;
$nb_total_revisions = 0;

foreach ($qcms as $qcm) {
    $evaluations = $DB->get_records('studentqcm_evaluation', array('question_id' => $qcm->id));
    $nb_total_revisions += count($evaluations);

    foreach ($evaluations as $evaluation) {
        if ($evaluation->grade != null ) {
            $nb_evaluated_revisions++;
        }
    }
}

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/teacher_production_eval.php', array('id' => $id, 'prod_id' => $prod_id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
    echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('production1', 'mod_studentqcm') . "</p>";
echo "</div>";

// Boutons de navigation
echo "<div class='flex mt-8 text-lg justify-between'>";
    echo "<a href='teacher_production_list.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
    echo "<i class='fas fa-arrow-left mr-2'></i>";
    echo get_string('back', 'mod_studentqcm');
    echo "</a>";
echo "</div>";

echo "<div class='grid grid-cols-2 gap-4 mt-4 text-xl text-gray-700 font-semibold text-center'>";
    echo "<p class='mr-8'>" . get_string('nb_evaluated_question', 'mod_studentqcm') . " : <span id='nb-eval-questions'>" . $nb_eval_questions . " / " . count($qcms) . "</span></p>";
    echo "<p>" . get_string('nb_evaluated_revision', 'mod_studentqcm') . " : <span id='nb-eval-revisions'>" . $nb_evaluated_revisions . " / " . $nb_total_revisions . "</span></p>";
echo "</div>";


if ($qcms) {
    echo "<div class='mt-4 space-y-4'>";

    foreach ($qcms as $qcm) {
        $nom_referentiel = isset($referentiels[$qcm->referentiel]) ? $referentiels[$qcm->referentiel] : get_string('unknown', 'mod_studentqcm');
        $nom_competency = isset($competencies[$qcm->competency]) ? $competencies[$qcm->competency] : get_string('unknown', 'mod_studentqcm');
        $nom_subcompetency = isset($subcompetencies[$qcm->subcompetency]) ? $subcompetencies[$qcm->subcompetency] : get_string('unknown', 'mod_studentqcm');
        $reponses = $DB->get_records('studentqcm_answer', array('question_id' => $qcm->id));
        $evaluations = $DB->get_records('studentqcm_evaluation', array('question_id' => $qcm->id));

        echo "<div class='bg-white rounded-3xl shadow flex items-center justify-between'>";

            echo "<div class='flex items-stretch w-full'>";

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
                    case 'POP':
                        $bgColor = 'bg-green-200';
                        $textColor = 'text-green-700';
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
                echo "<div class='flex flex-col justify-between p-4 w-full'>";

                    echo "<div class='grid grid-cols-2 gap-6'>";
                    
                        // Colonne de la question
                        echo "<div class='w-full'>"; 
                            echo "<p class='font-semibold text-2xl text-gray-700 mb-2'>";
                            echo format_string(ucfirst($qcm->question));
                            echo "</p>";
                    
                            // Infos référentiel, compétence, sous-compétence
                            echo "<div class='my-4 text-gray-600 text-sm space-y-1'>";
                                echo "<p class='flex items-center gap-2'>";
                                echo "<i class='fas fa-book text-green-500'></i>";
                                echo "<span>" . get_string('referentiel', 'mod_studentqcm') . ": <strong>" . ucfirst($nom_referentiel) . "</strong></span>";
                                echo "</p>";
                    
                                echo "<p class='flex items-center gap-2'>";
                                echo "<i class='fas fa-bookmark text-orange-500'></i>";
                                echo "<span>" . get_string('competency', 'mod_studentqcm') . ": <strong>" . ucfirst($nom_competency) . "</strong></span>";
                                echo "</p>";
                    
                                echo "<p class='flex items-center gap-2'>";
                                echo "<i class='fas fa-award text-purple-500'></i>";
                                echo "<span>" . get_string('subcompetency', 'mod_studentqcm') . ": <strong>" . ucfirst($nom_subcompetency) . "</strong></span>";
                                echo "</p>";
                            echo "</div>";

                            echo "<p class='font-semibold text-xl text-gray-700 mb-2'>";
                                echo "Contexte";
                            echo "</p>";
                            echo "<span class=''>{$qcm->context}</span>";

                            echo "<p class='font-semibold text-xl text-gray-700 mb-2 mt-4'>";
                                echo "Explication globale";
                            echo "</p>";
                            echo "<span class=''>{$qcm->global_comment}</span>";

                        echo "</div>";
                    
                        // Colonne des réponses
                        echo "<div class='w-full'>"; 
                        foreach ($reponses as $reponse) {
                            $bgColor = $reponse->istrue ? 'bg-lime-200' : 'bg-red-200';
                            $answerColor = $reponse->istrue ? 'text-lime-700' : 'text-red-700';

                            echo "<div class='w-full mb-2'>";
                            echo "<label class='flex flex-col w-full $bgColor p-3 rounded-lg'>";
                            echo "<span class='$answerColor font-medium text-lg'>{$reponse->answer}</span>";

                            // Affichage de l'explication avec une icône de flèche
                            if (!empty($reponse->explanation)) {
                                echo "<div class='flex mt-1 text-gray-700 text-md'>";
                                echo "<span class='mr-2 $answerColor'>&#10148;</span>";
                                echo "<span class='$answerColor'>{$reponse->explanation}</span>";
                                echo "</div>";
                            }

                            echo "</label>";
                            echo "</div>";
                        }
                        echo "</div>";
                    echo "</div>";

                    // Conteneur principal aligné en ligne
                    echo "<div class='bg-gray-50 rounded-lg p-4 my-2 flex items-center gap-4'>";
                        echo "<p class='font-semibold text-lg text-gray-700'>";
                        echo "<span>" . get_string('note_for_question', 'mod_studentqcm') . " :</span>";
                        echo "</p>";

                        echo "<div class='flex gap-2'>";
                            $baseClasses = "w-8 h-8 rounded-xl text-gray-700 text-sm font-medium flex items-center justify-center cursor-pointer transition-all duration-300 transform";
                            for ($i = 0; $i <= 5; $i++) {
                                $selected = ($qcm->grade == $i) ? 'bg-lime-500 text-white scale-105 shadow-lg' : 'bg-gray-200 hover:bg-gray-300 hover:shadow-md';
                                
                                echo "<button type='button' class='{$baseClasses} {$selected}' data-qcm-id='{$qcm->id}' onclick='selectGrade({$qcm->id}, {$i}, this)'>";

                                echo "<span class='text-md font-semibold'>" . ($i === 0 ? "Ø" : $i) . "</span>";
                                echo "</button>";
                            }
                        echo "</div>";
                    echo "</div>";

                    // Affichage des évaluations
                    echo "<div class='grid grid-cols-2 gap-6 mt-2'>";
                    if ($evaluations) {
                        foreach ($evaluations as $evaluation) {
                            echo "<div class='bg-gray-50 p-4 rounded-lg flex flex-col h-full'>";
                                echo "<p class='text-gray-700 text-center text-lg mb-2 font-semibold'>" . get_string('student_revision', 'mod_studentqcm') . " " . $evaluation->userid ."</p>";
                                echo "<p class='text-gray-500 mb-2'>{$evaluation->explanation}</p>";

                                echo "<div class='flex items-center gap-4 mt-auto'>";
                                    echo "<p class='text-gray-700 font-medium'>" . get_string('note_for_revision', 'mod_studentqcm') . " :</p>";
                                    echo "<div class='flex gap-2'>";
                                        for ($i = 0; $i <= 5; $i++) {
                                            $selected = ($evaluation->grade == $i) ? 'bg-indigo-400 text-white scale-105 shadow-lg' : 'bg-gray-200 hover:bg-gray-300 hover:shadow-md';
                                            
                                            echo "<button type='button' class='{$baseClasses} {$selected}' onclick='selectEvalGrade({$evaluation->id}, {$i}, event)'>";
                                            echo "<span class='text-md font-semibold'>" . ($i === 0 ? "Ø" : $i) . "</span>";
                                            echo "</button>";
                                        }
                                    echo "</div>";
                                echo "</div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<p class='text-gray-500'>" . get_string('no_evaluation_for_this_question', 'mod_studentqcm') . "</p>";
                    }
                    echo "</div>";
                echo "</div>";
            echo "</div>";
        echo "</div>";
    }
    echo "</div>";
    
} else {
    echo "<p class='text-center text-lg text-gray-600'>" . get_string('qcm_not_found', 'mod_studentqcm') . "</p>";
}

echo $OUTPUT->footer();

?>

<script>
function selectGrade(qcmId, grade, button) {
    // Désélectionner tous les boutons liés à cette question
    document.querySelectorAll(`[data-qcm-id="${qcmId}"]`).forEach(btn => {
        btn.classList.remove("bg-lime-500", "text-white", "scale-105", "shadow-lg");
        btn.classList.add("bg-gray-200", "hover:bg-gray-300", "hover:shadow-md", "text-gray-700");
    });

    // Appliquer les styles au bouton cliqué
    button.classList.remove("bg-gray-200", "hover:bg-gray-300", "hover:shadow-md", "text-gray-700");
    button.classList.add("bg-lime-500", "text-white", "scale-105", "shadow-lg");

    // Animation de clic
    button.style.transform = "scale(1.1)";
    setTimeout(() => {
        button.style.transform = "scale(1)";
    }, 100);

    // Envoyer la requête AJAX
    fetch(`save_grade.php?qcm_id=${qcmId}&grade=${grade}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log(`Note enregistrée pour la question ${qcmId}: ${grade}`);

                // Mettre à jour le compteur affiché
                document.getElementById('nb-eval-questions').textContent = data.nb_eval_questions;
            } else {
                console.error("Erreur : " + data.message);
                alert("Une erreur s'est produite lors de l'enregistrement.");
            }
        })
        .catch(error => console.error("Erreur lors de la requête :", error));
}



function selectEvalGrade(evalId, grade, event) {
    let button = event.currentTarget;

    // Désélectionner tous les boutons de la même évaluation
    document.querySelectorAll(`[onclick^="selectEvalGrade(${evalId},"]`).forEach(btn => {
        btn.classList.remove("bg-indigo-400", "text-white", "scale-105", "shadow-lg");
        btn.classList.add("bg-gray-200", "hover:bg-gray-300", "hover:shadow-md", "text-gray-700");
    });

    // Ajouter la sélection au bouton cliqué
    button.classList.remove("bg-gray-200", "hover:bg-gray-300", "hover:shadow-md", "text-gray-700");
    button.classList.add("bg-indigo-400", "text-white", "scale-105", "shadow-lg");

    // Effet de légère vibration au clic
    button.style.transform = "scale(1.1)";
    setTimeout(() => {
        button.style.transform = "scale(1)";
    }, 100);

    console.log(`Note pour l'évaluation ${evalId}: ${grade}`);

    // Enregistrer la note d'évaluation dans la table studentqcm_evaluation
    fetch(`save_eval_grade.php?eval_id=${evalId}&grade=${grade}`, { method: 'GET' })
        .then(response => response.json())
        .then(data => console.log('Grade d\'évaluation enregistré: ', data));
}
</script>

