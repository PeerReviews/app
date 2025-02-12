<?php
require_once(__DIR__ . '/../../config.php');

$subcompetency_id = required_param('subcompetency_id', PARAM_INT);

$keywords = $DB->get_records('keyword', array('subcompetency' => $subcompetency_id));

$keyword_data = [];

if ($keywords) {
    foreach ($keywords as $keyword) {
        $keyword_data[] = [
            'id' => $keyword->id,
            'word' => $keyword->word,
            'isCustom' => (int) $keyword->iscustom
        ];
    }
} else {
    $keyword_data[] = [
        'id' => '',
        'word' => 'Aucun mot clé disponible',
        'isCustom' => 0
    ];
}

// Retourner les données au format JSON
header('Content-Type: application/json');
echo json_encode($keyword_data);
exit;
