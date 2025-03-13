<?php
require_once(__DIR__ . '/../../config.php');

$question_id = required_param('question_id', PARAM_INT);

// On récupère les mots-clés associés à la question
$sql = "SELECT k.id
        FROM {pr_keyword} k
        JOIN {pr_question_keywords} qk ON k.id = qk.keyword_id
        WHERE qk.question_id = :question_id";

$selected_keywords = $DB->get_records_sql($sql, ['question_id' => $question_id]);

// On extrait seulement les IDs des mots-clés sélectionnés
$selected_keyword_ids = array_map(function($keyword) {
    return $keyword->id;
}, $selected_keywords);

// Si aucun mot-clé n'est sélectionné, renvoyer un tableau vide
if (empty($selected_keyword_ids)) {
    $selected_keyword_ids = [];
}

// Retourner les données au format JSON
header('Content-Type: application/json');
echo json_encode($selected_keyword_ids);
exit;
