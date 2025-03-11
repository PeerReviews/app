<?php

require_once('../../config.php');
global $DB;

// Récupérer les données envoyées
$data = json_decode(file_get_contents("php://input"), true);
$competenceName = $data['name'];
$subCompetences = $data['subCompetences'];

if (!$competenceName) {
    echo json_encode(['success' => false, 'error' => $name]);
    exit;
}

// Insérer la compétence
$competence = new stdClass();
$competence->name = $competenceName;
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

// // Supprimer les mots-clés liés à cette sous-compétence
// $DB->delete_records('keyword', ['subcompetency' => $subCompetenceId]);

// // Supprimer la sous-compétence
// $DB->delete_records('subcompetency', ['id' => $subCompetenceId]);

// require_once('../../config.php');
// global $DB;

// // Récupère les données envoyées avec FormData

// echo json_encode(['success' => false, 'error' => $_POST]);
// exit;

// $competenceName = isset($_POST['name']) ? $_POST['name'] : null;
// $subCompetences = isset($_POST['subCompetences']) ? $_POST['subCompetences'] : null;

// // Si l'un des champs est manquant, renvoie une erreur
// if (!$competenceName) {
//     echo json_encode(['success' => false, 'error' => 'Nom de compétence requis']);
//     exit;
// }

// if (!$subCompetences) {
//     echo json_encode(['success' => false, 'error' => 'Sous-compétences requises']);
//     exit;
// }

// // Insérer la compétence
// $competence = new stdClass();
// $competence->name = $competenceName;
// $competenceId = $DB->insert_record('competency', $competence);

// foreach ($subCompetences as $sub) {
//     $subCompetence = new stdClass();
//     $subCompetence->competency = $competenceId;
//     $subCompetence->name = trim($sub['name']);
//     $subCompetenceId = $DB->insert_record('subcompetency', $subCompetence);

//     foreach ($sub['keywords'] as $keyword) {
//         $keywordRecord = new stdClass();
//         $keywordRecord->subcompetency = $subCompetenceId;
//         $keywordRecord->word = trim($keyword);
//         $DB->insert_record('keyword', $keywordRecord);
//     }
// }

// // Traitement de la compétence
// echo json_encode(['success' => true, 'message' => 'Compétence ajoutée']);
// exit; 


?>
