<?php

require_once(__DIR__ . '/../../config.php');

$context = context_system::instance();

$id = required_param('id', PARAM_INT);
$prod_id = required_param('prod_id', PARAM_INT);

$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$session = $DB->get_record('studentqcm', ['archived' => 0], '*', MUST_EXIST);

$userid = $USER->id;

// Charger les noms des référentiels, compétences, sous-compétences et mots-clés
$referentiels = $DB->get_records_menu('referentiel', ['sessionid' => $session->id], '', 'id, name');
$competencies = $DB->get_records_menu('competency', ['sessionid' => $session->id], '', 'id, name');
$subcompetencies = $DB->get_records_menu('subcompetency', ['sessionid' => $session->id], '', 'id, name');

// Charger les questions de la production assignée
$qcms = array();

// Vérifier si l'ID de la production assignée est valide
if (!empty($prod_id)) {
    $questions = $DB->get_records('studentqcm_question', array('userid' => $prod_id, 'sessionid' => $session->id, 'status' => 1));

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


function generate_media_html($context, $filearea, $itemid, $file_storage) {
    $file_records = $file_storage->get_area_files($context->id, 'mod_studentqcm', $filearea, $itemid, 'sortorder', false);
    $media_html = '';

    foreach ($file_records as $file) {
        if ($file->get_filename() == '.') continue;

        $file_url = moodle_url::make_pluginfile_url($context->id, 'mod_studentqcm', $filearea, $itemid, $file->get_filepath(), $file->get_filename())->out();
        $file_extension = pathinfo($file->get_filename(), PATHINFO_EXTENSION);

        // Styles généraux pour tous les médias
        $common_classes = "cursor-pointer rounded-lg shadow";
        $overlay_classes = "absolute inset-0 flex items-center justify-center bg-black bg-opacity-40 rounded-lg opacity-0 hover:opacity-100 transition-opacity duration-300 cursor-pointer";
        $icon_classes = "text-white text-2xl";

        // Images
        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $media_html .= "
                <div class='relative block w-[40px] h-[40px]'>
                    <img src='{$file_url}' alt='{$file->get_filename()}' class='$common_classes w-full h-full object-cover' onclick='openMediaModal(\"{$file_url}\")' />
                    <div class='$overlay_classes' onclick='openMediaModal(\"{$file_url}\")'>
                        <i class='fas fa-search $icon_classes'></i>
                    </div>
                </div>";
        }
        // Vidéos
        elseif (in_array($file_extension, ['mp4', 'webm', 'ogg'])) {
            $media_html .= "
                <div class='relative block w-[40px] h-[40px]' onclick='openMediaModal(\"{$file_url}\")'>
                    <div class='$common_classes bg-gray-800 flex items-center justify-center w-full h-full'>
                        <i class='fas fa-play text-white text-xl'></i>
                    </div>
                    <div class='$overlay_classes'>
                        <i class='fas fa-play-circle $icon_classes'></i>
                    </div>
                </div>";
        }
        // Audios
        elseif (in_array($file_extension, ['mp3', 'wav', 'ogg'])) {
            $media_html .= "
                <div class='relative block w-[40px] h-[40px]' onclick='openMediaModal(\"{$file_url}\")'>
                    <div class='$common_classes bg-gray-700 flex items-center justify-center w-full h-full'>
                        <i class='fas fa-music text-white text-xl'></i>
                    </div>
                    <div class='$overlay_classes'>
                        <i class='fas fa-play-circle $icon_classes'></i>
                    </div>
                </div>";
        }
    }

    return $media_html;
}

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
            echo "<p>" . get_string('student', 'mod_studentqcm') . " " . $user_info->id . "</p>";
            echo "<p class='text-gray-700'>" . get_string('nb_evaluated_revision', 'mod_studentqcm') . " : 
                <span id='nb-eval-revisions-{$userid_eval}'>{$counts['evaluated']} / {$counts['total']}</span>
            </p>";
        echo "</div>";
    }    
echo "</div>";

$grid_eval_qcu = $studentqcm->grid_eval_qcu;
$grid_eval_qcm = $studentqcm->grid_eval_qcm;
$grid_eval_tcs = $studentqcm->grid_eval_tcs;

function get_bonus_malus($grid_eval_id) {
    global $DB;
    return $DB->get_record('grid_eval', array('id' => $grid_eval_id), '*');
}

$evaluation_types = ['grid_eval_qcu', 'grid_eval_qcm', 'grid_eval_tcs'];

echo "<div class='grid grid-cols-3 justify-between items-stretch gap-4 mt-4 text-gray-700'>";

foreach ($evaluation_types as $eval_type) {
    $grid_eval_id = $studentqcm->$eval_type;
    $grid_eval_record = get_bonus_malus($grid_eval_id);

    // Définir les couleurs en fonction du type d'évaluation
    if ($eval_type == 'grid_eval_qcu') {
        $border_color = 'border-sky-400';
        $title_color = 'text-sky-400';
    } elseif ($eval_type == 'grid_eval_qcm') {
        $border_color = 'border-lime-400';
        $title_color = 'text-lime-400';
    } elseif ($eval_type == 'grid_eval_tcs') {
        $border_color = 'border-indigo-400';
        $title_color = 'text-indigo-400';
    }

    if ($grid_eval_record) {
        $bonus_fields = ['bonus1', 'bonus2', 'bonus3', 'bonus4', 'bonus5'];
        $malus_fields = ['malus1', 'malus2', 'malus3', 'malus4', 'malus5'];

        echo "<div class='rounded-3xl shadow-md-lg bg-white p-4 flex flex-col'>";
        echo "<p class='text-center $title_color text-xl font-semibold'>" . get_string($eval_type, 'mod_studentqcm') . "</p>";

        echo "<div class='mt-4 text-md'>";
            foreach ($bonus_fields as $bonus) {
                echo "<div class='flex items-center'>";
                    echo "<p class='mr-2 text-lime-400 font-bold text-lg text-nowrap'>+ 1</p>";
                    echo "<p class='text-md leading-tight'>" . $grid_eval_record->$bonus . "</p>";
                echo "</div>";
            }
        echo "</div>";

        echo "<div class='mt-2 text-md'>";
            foreach ($malus_fields as $malus) {
                echo "<div class='flex items-center'>";
                    echo "<p class='mr-2 text-red-500 font-bold text-lg text-nowrap'>- 1</p>";
                    echo "<p class='text-md'>" . $grid_eval_record->$malus . "</p>";
                echo "</div>";
            }
        echo "</div>";

        echo "</div>";
    } else {
        // Si aucun enregistrement trouvé
        echo "<div class='text-center rounded-3xl shadow-md p-4 bg-white border-2 border-red-400'>";
        echo "<p>" . get_string('no_data', 'mod_studentqcm') . "</p>";
        echo "</div>";
    }
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
            $popInfo = $DB->get_record('question_pop', array('id' => $qcm->poptypeid, 'sessionid' => $session->id));
    
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
                    $bgColor = 'bg-amber-200';
                    $textColor = 'text-amber-400';
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
                            echo get_string('context', 'mod_studentqcm');
                            echo "</p>";
                            $context_sans_images = preg_replace('/<img[^>]*>/i', '', $qcm->context);
                            echo "<span class=''>{$context_sans_images}</span>";

                            $filearea = 'contextfiles';
                            $itemid = $qcm->id;

                            $file_storage = get_file_storage();

                            // Utilisation de la fonction pour récupérer les fichiers et les afficher
                            $img_text = generate_media_html($context, $filearea, $itemid, $file_storage);

                            // Affichage des fichiers associés au contexte de la question
                            if (!empty($img_text)) {
                                echo "<div class='flex items-center gap-2 mt-2'>";
                                echo "<p class='font-semibold text-md text-gray-600 inline-flex'>" . get_string('media_context', 'mod_studentqcm') . " :</p>";
                                echo "<div class='flex gap-1'>" . $img_text . "</div>";
                                echo "</div>";
                            }

                            echo "<p class='font-semibold text-xl text-gray-700 mb-2 mt-4'>";
                            echo get_string('global_comment', 'mod_studentqcm');
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
                            $answer_sans_images = preg_replace('/<img[^>]*>/i', '', $reponse->answer);
                            echo "<span class='$answerColor font-medium text-lg'>{$answer_sans_images}</span>";

                            if (!empty($reponse->explanation)) {
                                echo "<div class='flex mt-1 text-gray-700 text-md'>";
                                echo "<span class='mr-2 $answerColor'>&#10148;</span>";
                                $explanation_sans_images = preg_replace('/<img[^>]*>/i', '', $reponse->explanation);
                                echo "<span class='$answerColor'>{$explanation_sans_images}</span>";
                                echo "</div>";
                            }

                            $file_storage = get_file_storage();

                            // Affichage des fichiers "answerfiles"
                            $img_text_answer = generate_media_html($context, 'answerfiles', $reponse->id, $file_storage);
                            if (!empty($img_text_answer)) {
                                echo "<div class='flex items-center gap-2 mt-2'>";
                                echo "<p class='font-semibold text-md $answerColor inline-flex'>" . get_string('media_answer', 'mod_studentqcm') . " :</p>";
                                echo "<div class='flex gap-1'>" . $img_text_answer . "</div>";
                                echo "</div>";
                            }

                            // Affichage des fichiers "explanationfiles"
                            $img_text_explanation = generate_media_html($context, 'explanationfiles', $reponse->id, $file_storage);
                            if (!empty($img_text_explanation)) {
                                echo "<div class='flex items-center gap-2 mt-2'>";
                                echo "<p class='font-semibold text-md $answerColor inline-flex'>" . get_string('media_explanation', 'mod_studentqcm') . " :</p>";
                                echo "<div class='flex gap-1'>" . $img_text_explanation . "</div>";
                                echo "</div>";
                            }

                            echo "</label>";
                            echo "</div>";
                        }
                        echo "</div>";
                    echo "</div>";

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
                
                echo "<button class='w-full md:w-1/2 p-4 bg-amber-400 text-white font-semibold rounded-2xl hover:bg-amber-300 hover:shadow-md transition text-xl' onclick='applyFirstQcmGrades({$qcm->id})'>";
                echo "<span class='text-white font-bold'>" . get_string('autocomplete_question', 'mod_studentqcm') . "</span>";
                echo "</button>";
        
                echo "<button class='w-full md:w-1/2 p-4 bg-amber-400 text-white font-semibold rounded-2xl hover:bg-amber-300 hover:shadow-md transition text-xl' onclick='applyFirstQcmEvalGrades(" . json_encode($firstEvaluationId) . ")'>";
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

<!-- Modal d'affichage en grand -->
<div id="mediaModal" class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center hidden">
    <div class="relative bg-white p-4 rounded-lg shadow-lg max-w-3xl mt-12">
        <!-- Bouton de fermeture -->
        <button class="absolute top-2 right-2 text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded" onclick="closeMediaModal()">&times;</button>

        <!-- Conteneur dynamique pour médias -->
        <div id="modalContent" class="w-full flex justify-center items-center"></div>
    </div>
</div>

<script>
function openMediaModal(mediaUrl) {
    const modalContent = document.getElementById("modalContent");
    modalContent.innerHTML = ""; // On vide le contenu précédent

    const fileExtension = mediaUrl.split('.').pop().toLowerCase();

    if (["jpg", "jpeg", "png", "gif", "webp"].includes(fileExtension)) {
        // Afficher une image
        const img = document.createElement("img");
        img.src = mediaUrl;
        img.classList.add("max-w-full", "max-h-[80vh]", "mx-auto", "rounded-lg");
        modalContent.appendChild(img);
    } else if (["mp4", "webm", "ogg"].includes(fileExtension)) {
        // Afficher une vidéo
        const video = document.createElement("video");
        video.src = mediaUrl;
        video.controls = true;
        video.autoplay = true;
        video.classList.add("max-w-full", "max-h-[80vh]", "mx-auto", "rounded-lg");
        modalContent.appendChild(video);
    } else if (["mp3", "wav", "ogg"].includes(fileExtension)) {
        // Afficher un audio
        const audio = document.createElement("audio");
        audio.src = mediaUrl;
        audio.controls = true;
        audio.classList.add("w-full");
        modalContent.appendChild(audio);
    } else {
        // Fichier non supporté
        modalContent.innerHTML = "<p class='text-red-600 font-bold'>Format non supporté</p>";
    }

    // Afficher la modale
    document.getElementById("mediaModal").classList.remove("hidden");
    
    // Fermer en cliquant en dehors
    document.getElementById("mediaModal").addEventListener("click", function(event) {
        if (event.target === document.getElementById("mediaModal")) {
            closeMediaModal();
        }
    });
}

function closeMediaModal() {
    document.getElementById("mediaModal").classList.add("hidden");
}


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

