<?php

require_once(__DIR__ . '/../../config.php');

$context = context_system::instance();

$id = required_param('id', PARAM_INT);
$prod_id = required_param('prod_id', PARAM_INT);
$qcm_id = required_param('qcm_id', PARAM_INT);

$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$session = $DB->get_record('studentqcm', ['archived' => 0], '*', MUST_EXIST);

$userid = $USER->id;

// Récupérer la question avec ses métadonnées
$question = $DB->get_record('studentqcm_question', array('id' => $qcm_id, 'sessionid' => $session->id), '*', MUST_EXIST);
$referentiel = $DB->get_record('referentiel', array('id' => $question->referentiel, 'sessionid' => $session->id), '*');
$competency = $DB->get_record('competency', array('id' => $question->competency, 'sessionid' => $session->id), '*');
$subcompetency = $DB->get_record('subcompetency', array('id' => $question->subcompetency, 'sessionid' => $session->id), '*');
$keywords = $DB->get_record('question_keywords', array('question_id' => $qcm_id), 'keyword_id');
$reponses = $DB->get_records('studentqcm_answer', array('question_id' => $qcm_id));
$globalcomment = $DB->get_records('studentqcm_question', array('id' => $qcm_id, 'sessionid' => $session->id));

$keywords_list = [];
foreach ($keywords as $keyword_id) {
    $keyword = $DB->get_record('keyword', array('id' => $keyword_id, 'sessionid' => $session->id), 'word');
    if ($keyword) {
        $keywords_list[] = $keyword->word;
    }
}

$keywords_str = !empty($keywords_list) ? implode(', ', $keywords_list) : 'N/A';

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/eval_qcm_view.php', array('id' => $id, 'prod_id' => $prod_id, 'qcm_id' => $qcm_id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

// Vérifier si l'utilisateur a déjà évalué ce QCM
$evaluation = $DB->get_record('studentqcm_evaluation', array(
    'question_id' => $qcm_id,
    'userid' => $userid
));

// Récupérer le commentaire existant si une évaluation a déjà été soumise
$evaluation_comment = $evaluation ? $evaluation->explanation : '';

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
    echo "<a href='eval_qcm_list.php?id={$id}&prod_id={$prod_id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
    echo "<i class='fas fa-arrow-left mr-2'></i>";
    echo get_string('back', 'mod_studentqcm');
    echo "</a>";
echo "</div>";

echo "<div class='mx-auto mt-8'>";
    echo "<div class='mt-2 text-gray-600 text-sm flex flex-col space-y-1 rounded-3xl shadow p-4'>";

        // Référentiel
        echo "<p class='flex items-center gap-2 text-lg'>";
        echo "<i class='fas fa-book text-green-500'></i>";
        echo "<span> <strong>" . get_string('referentiel', 'mod_studentqcm') . ": </strong>" . ucfirst(($referentiel->name ?? 'N/A')) . "</span>";
        echo "</p>";

        // Compétence
        echo "<p class='flex items-center gap-2 text-lg'>";
        echo "<i class='fas fa-bookmark text-orange-500'></i>";
        echo "<span> <strong>" . get_string('competency', 'mod_studentqcm') . ": </strong>" . ucfirst(($competency->name ?? 'N/A')) . "</span>";
        echo "</p>";

        // Sous-compétence
        echo "<p class='flex items-center gap-2 text-lg'>";
        echo "<i class='fas fa-award text-purple-500'></i>";
        echo "<span> <strong>" . get_string('subcompetency', 'mod_studentqcm') . ": </strong>" . ucfirst(($subcompetency->name ?? 'N/A')) . "</span>";
        echo "</p>";

        // Mots clé
        echo "<p class='flex items-center gap-2 text-lg'>";
        echo "<i class='fas fa-thumbtack text-sky-500'></i>";
        echo "<span> <strong>" . get_string('subcompetency', 'mod_studentqcm') . ": </strong>" . ucfirst($keywords_str) . "</span>";
        echo "</p>";
    echo "</div>";

    echo "<div class='rounded-3xl bg-indigo-200 my-4 p-4'>";
        echo "<label for='context_1' class='block font-bold text-gray-600 text-lg'>" . get_string('context', 'mod_studentqcm') . " :</label>";
        echo "<span class='text-lg text-gray-600'>" . ucfirst($question->context) . "</span>";

        $file_storage = get_file_storage();
        $img_text = generate_media_html($context, 'contextfiles', $qcm_id, $file_storage);

        // Affichage des fichiers associés au contexte de la question
        if (!empty($img_text)) {
            echo "<div class='flex items-center gap-2 mt-2'>";
            echo "<p class='font-semibold text-md text-gray-600 inline-flex'>" . get_string('media_context', 'mod_studentqcm') . " :</p>";
            echo "<div class='flex gap-1'>" . $img_text . "</div>";
            echo "</div>";
        }
    echo "</div>";

    echo "<div class='rounded-3xl bg-sky-200 my-4 p-4'>";
        echo "<label for='context_1' class='block font-bold text-gray-600 text-lg'>" . get_string('question', 'mod_studentqcm') . " :</label>";
        echo "<div class='rounded-3xl w-full bg-sky-100 p-4'>";
            echo "<span class='text-lg text-gray-600'>" . ucfirst($question->question) . "</span>";
        echo "</div>";
        echo "<form id='qcm-form' class='mt-4'>";
            foreach ($reponses as $reponse) {
                echo "<label class='flex items-center space-x-2'>";
                    echo "<input type='checkbox' name='reponses[]' value='{$reponse->id}' class='h-4 w-4 text-lime-600 border-sky-300 rounded-lg focus:ring-lime-500'> ";
                    echo "<span class='text-gray-600 text-lg ml-2'>{$reponse->answer}</span>";
                    $file_storage = get_file_storage();
                    $img_text_answer = generate_media_html($context, 'answerfiles', $reponse->id, $file_storage);
                    if (!empty($img_text_answer)) {
                        echo "<div class='flex items-center gap-2'>";
                        echo "<div class='flex gap-1'>" . $img_text_answer . "</div>";
                        echo "</div>";
                    }
                echo "</label>";
            }
        echo "</form>";

        echo "<div id='correct-answers' style='display:none;' class='mt-4 rounded-3xl bg-sky-100 p-4'>";
            foreach ($reponses as $reponse) {
                echo "<div class='flex items-center gap-2 mb-2'>";
                    if ($reponse->istrue) {
                        echo "<p class='text-lime-600 text-lg'>✅ {$reponse->explanation}</p>";
                    } else {
                        echo "<p class='text-red-400 text-lg'>❌ {$reponse->explanation}</p>";
                    }

                    $file_storage = get_file_storage();
                    $img_text_answer = generate_media_html($context, 'explanationfiles', $reponse->id, $file_storage);

                    if (!empty($img_text_answer)) {
                        echo "<div class='flex gap-1'>" . $img_text_answer . "</div>";
                    }
                echo "</div>";
            }
        echo "</div>";

        echo "<div class='mt-4'>";
        echo "<button id='show-answers' class='px-4 py-2 bg-indigo-400 text-white text-lg font-semibold rounded-2xl hover:bg-indigo-500'>";
        echo "<i class='fas fa-magnifying-glass mr-2'></i> " . get_string('show_answer', 'mod_studentqcm');
        echo "</button>";
        echo "</div>";

    echo "</div>";

    echo "<div class='rounded-3xl bg-indigo-200 my-4 p-4'>";
        echo "<label for='context_1' class='block font-bold text-gray-600 text-lg'>" . get_string('global_comment', 'mod_studentqcm') . " :</label>";
        echo "<span class='text-lg text-gray-600'>" . ucfirst($question->global_comment) . "</span>";
    echo "</div>";

    echo "<div class='rounded-3xl bg-lime-200 my-4 p-4'>";
        echo "<label for='context_1' class='block font-bold text-gray-600 text-lg'>" . get_string('evaluate_comment', 'mod_studentqcm') . " :</label>";
        
        // Formulaire
        echo '<form id="evaluation-form" method="post" action="submit_evaluation.php?id=' . $id . '">';
        echo '<textarea id="evaluation_comment" name="evaluation_comment" rows="4" maxlength="5000" class="w-full p-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-lime-500 focus:outline-none resize-none">'
            . format_string($evaluation_comment) . '</textarea>';
        echo '<p id="char-count" class="text-gray-500 text-sm">0 / 5000</p>';
        echo '<input type="hidden" name="question_id" value="' . $question->id . '">';
        echo '<input type="hidden" name="prod_id" value="' . $prod_id . '">';
        echo '<input type="hidden" name="edit_mode" value="' . ($evaluation ? 1 : 0) . '">';
        echo '</form>';

        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            var textarea = document.getElementById('evaluation_comment');
            var charCount = document.getElementById('char-count');

            charCount.textContent = textarea.value.length + ' / 5000';

            textarea.addEventListener('input', function() {
                charCount.textContent = textarea.value.length + ' / 5000';
            });
        });
        </script>";


    echo "</div>";

    echo "<div class='mt-4 text-right'>";
        echo '<button onclick="document.getElementById(\'evaluation-form\').submit();" class="inline-block px-4 py-2 font-semibold rounded-2xl bg-lime-200 hover:bg-lime-300 cursor-pointer text-lime-700 no-underline text-lg">' .get_string('submit_evaluation', 'mod_studentqcm') . '</button>';
    echo "</div>";


echo "</div>";
echo "</div>";

echo $OUTPUT->footer();
?>

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
document.getElementById('show-answers').addEventListener('click', function() {
    let answers = document.getElementById('correct-answers');
    let button = document.getElementById('show-answers');

    if (answers.style.display === 'none' || answers.style.display === '') {
        answers.style.display = 'block';
        button.innerHTML = '<i class="fas fa-eye-slash mr-2"></i> Masquer les réponses';
    } else {
        answers.style.display = 'none';
        button.innerHTML = '<i class="fas fa-eye mr-2"></i> Afficher les réponses';
    }
});

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

</script>
