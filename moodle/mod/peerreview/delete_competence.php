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

$session_id = required_param('session_id', PARAM_INT);

// Supprimer les mots-clés liés aux sous-compétences
$subcompetencies = $DB->get_records('pr_subcompetency', ['competency' => $competenceId]);
foreach ($subcompetencies as $subcompetency) {
    $DB->delete_records('pr_keyword', ['sessionid' => $session_id, 'subcompetency' => $subcompetency->id]);
}

// Supprimer les sous-compétences
$DB->delete_records('pr_subcompetency', ['competency' => $competenceId]);

// Supprimer la compétence
$DB->delete_records('pr_competency', ['id' => $competenceId]);

echo json_encode(['success' => true]);

?>