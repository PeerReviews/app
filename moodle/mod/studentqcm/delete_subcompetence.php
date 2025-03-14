<?php
require_once('../../config.php');
global $DB;

// Récupérer les données envoyées
$data = json_decode(file_get_contents("php://input"), true);
$subCompetenceId = $data['subCompetenceId'];

if (!$subCompetenceId) {
    echo json_encode(['success' => false, 'error' => 'ID de sous-compétence manquant']);
    exit;
}

$session_id = required_param('session_id', PARAM_INT);

// Supprimer les mots-clés liés à cette sous-compétence
$DB->delete_records('keyword', ['sessionid' => $session_id, 'subcompetency' => $subCompetenceId]);

// Supprimer la sous-compétence
$DB->delete_records('subcompetency', ['id' => $subCompetenceId]);

echo json_encode(['success' => true]);

?>