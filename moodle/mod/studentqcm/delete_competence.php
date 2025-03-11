<?php
require_once('../../config.php');
global $DB;

// Récupérer les données envoyées
$data = json_decode(file_get_contents("php://input"), true);
$competenceId = $data['competenceId'];

if (!$competenceId) {
    echo json_encode(['success' => false, 'error' => 'ID de compétence manquant']);
    exit;
}

// Supprimer les mots-clés liés aux sous-compétences
$subcompetencies = $DB->get_records('subcompetency', ['competency' => $competenceId]);
foreach ($subcompetencies as $subcompetency) {
    $DB->delete_records('keyword', ['subcompetency' => $subcompetency->id]);
}

// Supprimer les sous-compétences
$DB->delete_records('subcompetency', ['competency' => $competenceId]);

// Supprimer la compétence
$DB->delete_records('competency', ['id' => $competenceId]);

echo json_encode(['success' => true]);

?>