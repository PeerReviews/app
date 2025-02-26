<?php
require_once(__DIR__ . '/../../config.php');

$competency_id = required_param('competency_id', PARAM_INT);

// Obtenir les sous-compétences associées à cette compétence
$subcompetencies = $DB->get_records('subcompetency', array('competency' => $competency_id));

$subcompetency_data = [];

if ($subcompetencies) {
    foreach ($subcompetencies as $subcompetency) {
        $subcompetency_data[] = [
            'id' => $subcompetency->id,
            'name' => $subcompetency->name,
            'isCustom' => (int) $subcompetency->iscustom
        ];
    }
} else {
    $subcompetency_data[] = [
        'id' => '',
        'name' => 'Aucune sous-compétence disponible',
        'isCustom' => 0
    ];
}

header('Content-Type: application/json');
echo json_encode($subcompetency_data);
?>
