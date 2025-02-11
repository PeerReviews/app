<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
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

$PAGE->set_url('/mod/studentqcm/eval_qcm_view.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

echo $OUTPUT->header();

echo "<p><strong>Référentiel :</strong> " . ($referentiel->name ?? 'N/A') . "</p>";
echo "<p><strong>Compétence :</strong> " . ($competency->name ?? 'N/A') . "</p>";
echo "<p><strong>Sous-compétence :</strong> " . ($subcompetency->name ?? 'N/A') . "</p>";
echo "<p><strong>Mots-clés :</strong> " . $keywords_str . "</p>";
echo "<p><strong>Contexte :</strong> " . $question->context . "</p>";
echo "<p><strong>Question :</strong> " . $question->question . "</p>";

echo "<form id='qcm-form'>";
foreach ($reponses as $reponse) {
    echo "<label><input type='checkbox' name='reponses[]' value='{$reponse->id}'> {$reponse->answer}</label><br>";
}
echo "</form>";


echo "<button id='show-answers'>Afficher les réponses</button>";
echo "<div id='correct-answers' style='display:none;'>";
foreach ($reponses as $reponse) {
    if ($reponse->istrue) {
        echo "<p style='color:green;'>✅ {$reponse->explanation}</p>";
    }
    else {
        echo "<p style='color:red;'>❌ {$reponse->explanation}</p>";
    }
}
echo "<p><strong>Commentaire global :</strong> " . $question->global_comment . "</p>";
echo "</div>";

echo "<h3>Commentaire d'évaluation :</h3>";
echo "<textarea id='evaluation-comment' rows='4' cols='50'></textarea>";
echo $OUTPUT->footer();

?>

<script>
document.getElementById('show-answers').addEventListener('click', function() {
    let answers = document.getElementById('correct-answers');
    answers.style.display = (answers.style.display === 'none' || answers.style.display === '') ? 'block' : 'none';
});
</script>
