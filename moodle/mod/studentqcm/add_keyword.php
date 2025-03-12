<?php
require_once(__DIR__ . '/../../config.php');

header('Content-Type: application/json');

// Vérifier la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Requête invalide']);
    exit;
}

$session = $DB->get_record('studentqcm', ['archived' => 0], '*', MUST_EXIST);

// Récupérer et nettoyer les données
$word = isset($_POST['word']) ? trim($_POST['word']) : '';
$subcompetency_id = isset($_POST['subcompetency_id']) ? intval($_POST['subcompetency_id']) : 0;

if (empty($word) || $subcompetency_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Mot-clé ou sous-compétence manquante.']);
    exit;
}

// Vérifier que la sous-compétence existe
$subcompetency = $DB->get_record('subcompetency', ['id' => $subcompetency_id]);
if (!$subcompetency) {
    echo json_encode(['success' => false, 'message' => 'Sous-compétence non trouvée.']);
    exit;
}

$record = new stdClass();
$record->word = $word;
$record->subcompetency = $subcompetency_id;
$record->sessionid = $session->id;
$record->isCustom = 1;

try {
    $new_id = $DB->insert_record('keyword', $record, true);
    echo json_encode([
        'success' => true,
        'id' => $new_id,
        'word' => $word
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout : ' . $e->getMessage()]);
}
exit;
?>
