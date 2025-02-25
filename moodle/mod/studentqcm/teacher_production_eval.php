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

$user_evaluations = [];

foreach ($qcms as $qcm) {
    $evaluations = $DB->get_records('studentqcm_evaluation', array('question_id' => $qcm->id));

    foreach ($evaluations as $evaluation) {
        $userid_eval = $evaluation->userid;

        if (!isset($user_evaluations[$userid_eval])) {
            $user_evaluations[$userid_eval] = ['evaluated' => 0, 'total' => 0];
        }

        $user_evaluations[$userid_eval]['total']++;

        if ($evaluation->grade !== null) {
            $user_evaluations[$userid_eval]['evaluated']++;
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


echo "<div class='flex justify-between items-center gap-4 mt-4 text-xl text-gray-700 font-semibold text-center'>";
    echo "<div class='flex-1 text-center rounded-3xl shadow-md p-4 bg-gray-50'>";
        echo "<p>" . get_string('student', 'mod_studentqcm') . " " . $prod_id . "</p>";
        echo "<p class='text-gray-700'>" . get_string('nb_evaluated_question', 'mod_studentqcm') . " : 
            <span id='nb-eval-questions'>" . $nb_eval_questions . " / " . count($qcms) . "</span>
        </p>";
    echo "</div>";

    foreach ($user_evaluations as $userid_eval => $counts) {
        $user_info = $DB->get_record('user', array('id' => $userid_eval));
    
        echo "<div class='flex-1 text-center rounded-3xl shadow-md p-4 bg-gray-50'>";
            echo "<p>" . get_string('student', 'mod_studentqcm') . "</p>";
            echo "<p class='text-gray-700'>" . get_string('nb_evaluated_revision', 'mod_studentqcm') . " " . $user_info->id . " : 
                <span id='nb-eval-revisions-{$userid_eval}'>{$counts['evaluated']} / {$counts['total']}</span>
            </p>";
        echo "</div>";
    }    
echo "</div>";

if ($qcms) {
    echo "<div class='mt-4 space-y-4'>";

    // Trier les questions par popTypeId
    usort($qcms, function($a, $b) {
        return $a->poptypeid <=> $b->poptypeid;
    });

    $previousPopTypeId = null;

    foreach ($qcms as $index => $qcm) {
        $nom_referentiel = isset($referentiels[$qcm->referentiel]) ? $referentiels[$qcm->referentiel] : get_string('unknown', 'mod_studentqcm');
        $nom_competency = isset($competencies[$qcm->competency]) ? $competencies[$qcm->competency] : get_string('unknown', 'mod_studentqcm');
        $nom_subcompetency = isset($subcompetencies[$qcm->subcompetency]) ? $subcompetencies[$qcm->subcompetency] : get_string('unknown', 'mod_studentqcm');
        $reponses = $DB->get_records('studentqcm_answer', array('question_id' => $qcm->id));
        $evaluations = $DB->get_records('studentqcm_evaluation', array('question_id' => $qcm->id));

        // Vérifier si le popTypeId a changé pour insérer une séparation
        if ($qcm->poptypeid !== $previousPopTypeId) {
            $popInfo = $DB->get_record('question_pop', array('id' => $qcm->poptypeid));
    
            if ($popInfo) {
                echo "<h2 class='text-xl font-semibold text-gray-700 text-center my-4 bg-gray-100 p-2 rounded-lg'>";
                echo "POP - {$popInfo->nbqcm} QCM, {$popInfo->nbqcu} QCU";
                echo "</h2>";
            } else {
                echo "<h2 class='text-xl font-semibold text-gray-700 text-center my-4 bg-gray-100 p-2 rounded-lg'>";
                echo "POP - " . get_string(ucfirst(unavailable_information));
                echo "</h2>";
            }
    
            $previousPopTypeId = $qcm->poptypeid;
        }

        echo "<div class='bg-white rounded-3xl shadow flex items-center justify-between'>";

            echo "<div class='flex items-stretch w-full'>";

                // Définir les couleurs en fonction du type de question
                if ($qcm->ispop == 1) {
                    $bgColor = 'bg-yellow-200';
                    $textColor = 'text-yellow-400';
                } else {
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
                }

                $displayType = ($qcm->ispop == 1) ? "POP - " . ucfirst($qcm->type) : ucfirst($qcm->type);

                // Type de question
                echo "<div class='{$bgColor} rounded-l-3xl py-4 px-2 flex items-center w-16 justify-center'>"; 
                echo "<p class='font-semibold text-2xl {$textColor} flex items-center gap-2 -rotate-90 whitespace-nowrap'>";
                echo format_string($displayType);
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
                                            
                                            echo "<button type='button' class='{$baseClasses} {$selected}' data-eval-qcm-id='{$qcm->id}' onclick='selectEvalGrade({$evaluation->id}, {$i}, event)'>";
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

        if ($index === 0) {
            echo "<div class='bg-gray-50 rounded-lg p-4 my-4 text-center'>";
            echo "<button class='px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition' onclick='applyFirstQcmGrades({$qcm->id})'>";
            echo "Appliquer ces notes à toutes les questions suivantes";
            echo "</button>";
            echo "</div>";            
        }

        if ($index === 0) {
            echo "<div class='bg-gray-50 rounded-lg p-4 my-4 text-center'>";
            echo "<button class='px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 transition' onclick='applyFirstQcmEvaluations({$qcm->id})'>";
            echo "Appliquer ces évaluations à toutes les révisions suivantes";
            echo "</button>";
            echo "</div>";
        }
        
    }
    echo "</div>";
    
} else {
    echo "<p class='text-center text-lg text-gray-600'>" . get_string('qcm_not_found', 'mod_studentqcm') . "</p>";
}

echo $OUTPUT->footer();

?>

<script>
function selectGrade(qcmId, grade, button) {
    document.querySelectorAll(`[data-qcm-id="${qcmId}"]`).forEach(btn => {
        btn.classList.remove("bg-lime-500", "text-white", "scale-105", "shadow-lg");
        btn.classList.add("bg-gray-200", "hover:bg-gray-300", "hover:shadow-md", "text-gray-700");
    });

    button.classList.remove("bg-gray-200", "hover:bg-gray-300", "hover:shadow-md", "text-gray-700");
    button.classList.add("bg-lime-500", "text-white", "scale-105", "shadow-lg");

    button.style.transform = "scale(1.1)";
    setTimeout(() => {
        button.style.transform = "scale(1)";
    }, 100);

    fetch(`save_grade.php?qcm_id=${qcmId}&grade=${grade}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                let evalCounter = document.getElementById('nb-eval-questions');
                let total = evalCounter.textContent.split('/')[1].trim();
                evalCounter.textContent = `${data.nb_eval_questions} / ${total}`;
            } else {
                alert("Une erreur s'est produite lors de l'enregistrement.");
            }
        })
        .catch(error => console.error("Erreur lors de la requête :", error));
}


function selectEvalGrade(evalId, grade, event) {
    let button = event.currentTarget;

    document.querySelectorAll(`[onclick^="selectEvalGrade(${evalId}"]`).forEach(btn => {
        btn.classList.remove("bg-indigo-400", "text-white", "scale-105", "shadow-lg");
        btn.classList.add("bg-gray-200", "hover:bg-gray-300", "hover:shadow-md", "text-gray-700");
    });

    button.classList.remove("bg-gray-200", "hover:bg-gray-300", "hover:shadow-md", "text-gray-700");
    button.classList.add("bg-indigo-400", "text-white", "scale-105", "shadow-lg");

    button.style.transform = "scale(1.1)";
    setTimeout(() => {
        button.style.transform = "scale(1)";
    }, 100);


    // Envoi de la requête AJAX pour enregistrer la note
    fetch(`save_eval_grade.php?eval_id=${evalId}&grade=${grade}&prod_id=<?php echo $prod_id; ?>`, { method: 'GET' })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const evaluatedSpan = document.getElementById(`nb-eval-revisions-${data.user_id}`);
                if (evaluatedSpan) {
                    let total = evaluatedSpan.textContent.split('/')[1].trim();
                    evaluatedSpan.textContent = `${data.evaluated} / ${total}`;
                }
            } else {
                alert("Une erreur s'est produite lors de l'enregistrement.");
            }
        })
        .catch(error => console.error("Erreur lors de la requête :", error));
}



function applyFirstQcmGrades(firstQcmId) {
    let firstQcmButtons = document.querySelectorAll(`[data-qcm-id="${firstQcmId}"]`);
    let selectedGrade = null;

    // Récupérer la note sélectionnée dans le premier QCM
    firstQcmButtons.forEach(btn => {
        if (btn.classList.contains("bg-lime-500")) {
            selectedGrade = parseInt(btn.textContent.trim());
        }
    });

    if (selectedGrade === null) {
        alert("Veuillez noter la première question avant d'appliquer aux autres !");
        return;
    }

    // Appliquer la note récupérée aux autres QCM (questions)
    document.querySelectorAll("[data-qcm-id]").forEach(btn => {
        let qcmId = btn.getAttribute("data-qcm-id");
        if (qcmId != firstQcmId && parseInt(btn.textContent.trim()) === selectedGrade) {
            selectGrade(qcmId, selectedGrade, btn);
        }
    });
}


function applyFirstQcmEvaluations(firstQcmId) {
    let firstQcmRevisions = document.querySelectorAll(`[data-eval-qcm-id="${firstQcmId}"]`);
    let selectedGrades = [];

    // Récupérer les évaluations sélectionnées dans les révisions
    firstQcmRevisions.forEach(btn => {
        if (btn.classList.contains("bg-indigo-400")) {
            selectedGrades.push(parseInt(btn.textContent.trim()));
        }
    });

    console.log("selectedGrades", selectedGrades); // Vérification

    // Vérifier que deux révisions sont sélectionnées
    if (selectedGrades.length !== 2) {
        alert("Veuillez noter les deux premières révisions avant d'appliquer aux autres !");
        return;
    }

    // Appliquer les notes récupérées aux révisions des autres questions
    document.querySelectorAll("[data-eval-qcm-id]").forEach(btn => {
        let evalQcmId = btn.getAttribute("data-eval-qcm-id");  // Correction de l'attribut ici
        let evalId = btn.dataset.evalQcmId; // Récupérer l'ID d'évaluation spécifique
        if (evalQcmId !== firstQcmId) {
            console.log('btn:', btn); // Inspecte l'élément
            selectedGrades.forEach(grade => {
                console.log('evalId:', evalId, 'grade:', grade, 'button:', btn);
                selectEvalGrade(evalId, grade, btn);  // Passer l'ID d'évaluation correct
            });
        }
    });
}



</script>

