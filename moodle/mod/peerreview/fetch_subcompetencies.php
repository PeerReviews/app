<?php
require_once(__DIR__ . '/../../config.php');

$competency_id = required_param('competency_id', PARAM_INT);

// Obtenir les sous-compétences associées à cette compétence
$subcompetencies = $DB->get_records('pr_subcompetency', array('competency' => $competency_id));

$subcompetency_data = [];

if ($subcompetencies) {
    foreach ($subcompetencies as $subcompetency) {
        $subcompetency_data[] = [
            'id' => $subcompetency->id,
            'name' => $subcompetency->name,
            'iscustom' => (int) $subcompetency->iscustom
        ];
    }
} else {
    $subcompetency_data[] = [
        'id' => '',
        'name' => 'Aucune sous-compétence disponible',
        'iscustom' => 0
    ];
}

header('Content-Type: application/json');
echo json_encode($subcompetency_data);
?>
