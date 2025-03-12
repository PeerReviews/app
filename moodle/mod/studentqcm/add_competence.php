<?php

require_once('../../config.php');
global $DB;

// Récupérer les données envoyées
$data = json_decode(file_get_contents("php://input"), true);
$referentiel = $data['referentiel'];
$competenceName = $data['name'];
$subCompetences = $data['subCompetences'];

$session_id = required_param('session_id', PARAM_INT);

if (!$competenceName) {
    echo json_encode(['success' => false, 'error' => $name]);
    exit;
}

// Insérer la compétence
$competence = new stdClass();
$competence->name = $competenceName;
$competence->referentiel = $referentiel;
$competence->sessionid = $session_id;
$competenceId = $DB->insert_record('competency', $competence);

foreach ($subCompetences as $sub) {
    $subCompetence = new stdClass();
    $subCompetence->competency = $competenceId;
    $subCompetence->name = trim($sub['name']);
    $subCompetence->sessionid = $session->id;
    $subCompetenceId = $DB->insert_record('subcompetency', $subCompetence);

    foreach ($sub['keywords'] as $keyword) {
        $keywordRecord = new stdClass();
        $keywordRecord->subcompetency = $subCompetenceId;
        $keywordRecord->word = trim($keyword);
        $keywordRecord->sessionid = $session->id;
        $DB->insert_record('keyword', $keywordRecord);
    }
}

// Traitement de la compétence
echo json_encode(['success' => true, 'message' => 'Compétence ajoutée']);

?>
