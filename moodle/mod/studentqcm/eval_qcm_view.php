<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$prod_id = required_param('prod_id', PARAM_INT);
$qcm_id = required_param('qcm_id', PARAM_INT);

$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$userid = $USER->id;

// Récupérer la question avec ses métadonnées
$question = $DB->get_record('studentqcm_question', array('id' => $qcm_id), '*', MUST_EXIST);
$referentiel = $DB->get_record('referentiel', array('id' => $question->referentiel), '*');
$competency = $DB->get_record('competency', array('id' => $question->competency), '*');
$subcompetency = $DB->get_record('subcompetency', array('id' => $question->subcompetency), '*');
$keywords = $DB->get_record('question_keywords', array('question_id' => $qcm_id), 'keyword_id');
$reponses = $DB->get_records('studentqcm_answer', array('question_id' => $qcm_id));
$globalcomment = $DB->get_records('studentqcm_question', array('id' => $qcm_id));

$keywords_list = [];
foreach ($keywords as $keyword_id) {
    $keyword = $DB->get_record('keyword', array('id' => $keyword_id), 'word');
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
                echo "</label>";
            }
        echo "</form>";

        echo "<div id='correct-answers' style='display:none;' class='mt-4 rounded-3xl bg-sky-100 p-4'>";
            foreach ($reponses as $reponse) {
                if ($reponse->istrue) {
                    echo "<p class='text-lime-600 text-lg mb-2'>✅ {$reponse->explanation}</p>";
                }
                else {
                    echo "<p class='text-red-400 text-lg mb-2'>❌ {$reponse->explanation}</p>";
                }
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
        // Label du commentaire
        echo "<label for='context_1' class='block font-bold text-gray-600 text-lg'>" . get_string('evaluate_comment', 'mod_studentqcm') . " :</label>";
        
        // Formulaire
        echo '<form id="evaluation-form" method="post" action="submit_evaluation.php?id=' . $id . '">';
            echo '<textarea name="evaluation_comment" rows="4" class="w-full p-2 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-lime-500 focus:outline-none resize-none">'
                . format_string($evaluation_comment) . '</textarea>';
            echo '<input type="hidden" name="question_id" value="' . $question->id . '">';
            echo '<input type="hidden" name="prod_id" value="' . $prod_id . '">';
            echo '<input type="hidden" name="edit_mode" value="' . ($evaluation ? 1 : 0) . '">';
        echo '</form>';

    echo "</div>";

    echo "<div class='mt-4 text-right'>";
        echo '<button onclick="document.getElementById(\'evaluation-form\').submit();" class="inline-block px-4 py-2 font-semibold rounded-2xl bg-lime-200 hover:bg-lime-300 cursor-pointer text-lime-700 no-underline text-lg">' .get_string('submit_evaluation', 'mod_studentqcm') . '</button>';
    echo "</div>";


echo "</div>";
echo "</div>";

echo $OUTPUT->footer();
?>

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

</script>
