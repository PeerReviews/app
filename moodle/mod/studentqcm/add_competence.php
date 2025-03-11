<?php

require_once('../../config.php');
global $DB;

// Récupérer les données envoyées
$data = json_decode(file_get_contents("php://input"), true);
$referentiel = $data['referentiel'];
$competenceName = $data['name'];
$subCompetences = $data['subCompetences'];

if (!$competenceName) {
    echo json_encode(['success' => false, 'error' => $name]);
    exit;
}

// Insérer la compétence
$competence = new stdClass();
$competence->name = $competenceName;
$competence->referentiel = $referentiel;
$competenceId = $DB->insert_record('competency', $competence);

foreach ($subCompetences as $sub) {
    $subCompetence = new stdClass();
    $subCompetence->competency = $competenceId;
    $subCompetence->name = trim($sub['name']);
    $subCompetenceId = $DB->insert_record('subcompetency', $subCompetence);

    foreach ($sub['keywords'] as $keyword) {
        $keywordRecord = new stdClass();
        $keywordRecord->subcompetency = $subCompetenceId;
        $keywordRecord->word = trim($keyword);
        $DB->insert_record('keyword', $keywordRecord);
    }
}

// Traitement de la compétence
echo json_encode(['success' => true, 'message' => 'Compétence ajoutée']);

?>
