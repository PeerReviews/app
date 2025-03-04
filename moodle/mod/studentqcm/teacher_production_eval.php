<?php

require_once(__DIR__ . '/../../config.php');

$context = context_system::instance();

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
    $questions = $DB->get_records('studentqcm_question', array('userid' => $prod_id));

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

        $firstEvaluationId = array();

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

                            $filearea = 'contextfiles';            // La zone des fichiers associée au contexte
                            $itemid = $qcm->id;            // L'ID de la question

                            $file_storage = get_file_storage();

                            // Récupérer tous les fichiers associés au contexte de la question
                            $file_records = $file_storage->get_area_files(
                                $context->id,      // ID du contexte
                                'mod_studentqcm',  // Nom du module
                                $filearea,         // Zone de fichiers
                                $itemid,           // ID de l'élément
                                'sortorder',       // Tri des fichiers
                                false              // Inclure ou non les fichiers supprimés
                            );

                            // Parcourir les fichiers et générer les balises <img> pour les afficher
                            $img_text = '';
                            foreach ($file_records as $file) {
                                if ($file->get_filename() == '.') {
                                    continue;
                                }
                            
                                $img_url = moodle_url::make_pluginfile_url(
                                    $context->id,
                                    'mod_studentqcm',
                                    $filearea,
                                    $itemid,
                                    $file->get_filepath(),
                                    $file->get_filename()
                                )->out();
                            
                                echo "<p>URL générée : <a href='{$img_url}' target='_blank'>{$img_url}</a></p>";
                            
                                $img_text .= "<img src='{$img_url}' alt='{$file->get_filename()}' style='max-width:100%; height:auto;' />";
                            }


// debugging("URL du fichier : " . moodle_url::make_pluginfile_url(
//     $context->id,
//     'mod_studentqcm',
//     $filearea,
//     $itemid,
//     $file->get_filepath(),
//     $file->get_filename()
// )->out(), DEBUG_DEVELOPER);

                            
                            echo $img_text;

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
                                            
                                            echo "<button type='button' class='{$baseClasses} {$selected}' data-eval-id='{$evaluation->id}' data-user-id='{$evaluation->userid}' onclick='selectEvalGrade({$evaluation->id}, {$i}, event)'>";
                                            echo "<span class='text-md font-semibold'>" . ($i === 0 ? "Ø" : $i) . "</span>";
                                            echo "</button>";
                                        }
                                    echo "</div>";
                                echo "</div>";
                            echo "</div>";

                            if ($index === 0) {
                                array_push($firstEvaluationId, $evaluation->id);
                            }
                        }
                    } else {
                        echo "<p class='text-gray-500'>" . get_string('no_evaluation_for_this_question', 'mod_studentqcm') . "</p>";
                    }
                    echo "</div>";
                echo "</div>";
            echo "</div>";
        echo "</div>";

        if ($index === 0) {
            echo "<div class='my-6 mx-4 text-center flex flex-col md:flex-row gap-4 justify-between items-center'>";
                
                echo "<button class='w-full md:w-1/2 p-4 bg-yellow-400 text-white font-semibold rounded-2xl hover:bg-yellow-300 hover:shadow-md transition text-xl' onclick='applyFirstQcmGrades({$qcm->id})'>";
                echo "<span class='text-white font-bold'>" . get_string('autocomplete_question', 'mod_studentqcm') . "</span>";
                echo "</button>";
        
                echo "<button class='w-full md:w-1/2 p-4 bg-yellow-400 text-white font-semibold rounded-2xl hover:bg-yellow-300 hover:shadow-md transition text-xl' onclick='applyFirstQcmEvalGrades(" . json_encode($firstEvaluationId) . ")'>";
                echo "<span class='text-white font-bold'>" . get_string('autocomplete_review', 'mod_studentqcm') . "</span>";
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

<!-- Modal de confirmation auto-complete question-->
<div id="auto-complete-question-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
    <div class="bg-white rounded-3xl py-8 px-12 max-w-lg w-full shadow-xl transform transition-all scale-95 hover:scale-100 relative flex flex-col items-center justify-center">
        <h3 class="text-2xl font-semibold text-lime-500 my-4 text-center">
            <?php echo get_string('autocomplete_question_success', 'mod_studentqcm'); ?>
        </h3>
        <button id="close-auto-complete-question-modal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 font-bold text-2xl transition-all duration-300 ease-in-out transform hover:scale-110">
            &times;
        </button>
    </div>
</div>

<!-- Modal de confirmation auto-complete révisions-->
<div id="auto-complete-review-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
    <div class="bg-white rounded-3xl py-8 px-12 max-w-lg w-full shadow-xl transform transition-all scale-95 hover:scale-100 relative flex flex-col items-center justify-center">
        <h3 class="text-2xl font-semibold text-lime-500 my-4 text-center">
            <?php echo get_string('autocomplete_review_success', 'mod_studentqcm'); ?>
        </h3>
        <button id="close-auto-complete-review-modal" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 font-bold text-2xl transition-all duration-300 ease-in-out transform hover:scale-110">
            &times;
        </button>
    </div>
</div>


<script>

function showQuestionModal() {
    document.getElementById("auto-complete-question-modal").classList.remove("hidden");
}

// Fonction pour fermer le modal
function closeQuestionModal() {
    document.getElementById("auto-complete-question-modal").classList.add("hidden");
}

document.getElementById("close-auto-complete-question-modal").addEventListener("click", function() {
    closeQuestionModal();
});

document.getElementById("auto-complete-question-modal").addEventListener("click", function(event) {
    if (event.target === this) {
        closeQuestionModal();
    }
});

function showReviewModal() {
    document.getElementById("auto-complete-review-modal").classList.remove("hidden");
}

// Fonction pour fermer le modal
function closeReviewModal() {
    document.getElementById("auto-complete-review-modal").classList.add("hidden");
}

document.getElementById("close-auto-complete-review-modal").addEventListener("click", function() {
    closeReviewModal();
});

document.getElementById("auto-complete-review-modal").addEventListener("click", function(event) {
    if (event.target === this) {
        closeReviewModal();
    }
});


    
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


function selectEvalGradeByButton(evalId, grade, button) {

    document.querySelectorAll(`[data-eval-id="${evalId}"]`).forEach(btn => {
        if (parseInt(btn.textContent.trim()) !== grade) {
            btn.classList.remove("bg-indigo-400", "text-white", "scale-105", "shadow-lg");
            btn.classList.add("bg-gray-200", "hover:bg-gray-300", "hover:shadow-md", "text-gray-700");
        }
        else {
            // Appliquer le style sélectionné au bouton cliqué
            button.classList.remove("bg-gray-200", "hover:bg-gray-300", "hover:shadow-md", "text-gray-700");
            button.classList.add("bg-indigo-400", "text-white", "scale-105", "shadow-lg");
        }
    });

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

    showQuestionModal();

}


function getUserIdByEvalId(evalId) {
    return fetch(`fetch_userId_eval.php?evalId=${evalId}`)
        .then(response => response.json())
        .then(data => {
            if (data.userid) {
                return data.userid;
            } else {
                throw new Error('User ID not found');
            }
        })
        .catch(error => {
            console.error('Error fetching user ID:', error);
            return null;
        });
}

function applyFirstQcmEvalGrades(firstQcmIds) {

    let selectedGrades = [];

    let fetchPromises = firstQcmIds.map(qcmId => {
        let qcmButtons = document.querySelectorAll(`[data-eval-id="${qcmId}"]`);
        let evalId = qcmId;

        return getUserIdByEvalId(evalId).then(userId => {
            if (userId) {
                let selectedGrade = null;

                qcmButtons.forEach(btn => {
                    if (btn.classList.contains("bg-indigo-400")) {
                        selectedGrade = parseInt(btn.textContent.trim());
                    }
                });

                if (selectedGrade !== null) {
                    selectedGrades.push({ userId: userId, qcmId: qcmId, grade: selectedGrade });
                } else {
                    alert(`Veuillez noter les révisions avant d'appliquer la même note aux révisions suivantes !`);
                }
            }
        });
    });

    Promise.all(fetchPromises).then(() => {
        applyGradesToUsers(selectedGrades);
    });

    showReviewModal();
}


function applyGradesToUsers(selectedGrades) {
    selectedGrades.forEach(gradeEntry => {
        let { userId, grade } = gradeEntry;

        let userElements = document.querySelectorAll(`[data-user-id="${userId}"]`);

        userElements.forEach(el => {
            let evalId = el.getAttribute('data-eval-id');
            selectEvalGradeByButton(evalId, grade, el);
        });
    });
}




</script>

