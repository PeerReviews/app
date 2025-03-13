<?php
require_once(__DIR__ . '/../../config.php');
// Inclure les fichiers nécessaires pour se connecter à la base de données

global $DB;

// Vérifier si l'ID d'évaluation (eval_id) est passé en paramètre
if (isset($_GET['evalId'])) {
    $evalId = $_GET['evalId'];

    // Interroger la base de données pour obtenir l'utilisateur associé à cet eval_id
    $record = $DB->get_record('pr_evaluation', array('id' => $evalId), 'userid');

    // Si un enregistrement est trouvé, renvoyer l'ID utilisateur
    if ($record) {
        echo json_encode(['userid' => $record->userid]);
    } else {
        echo json_encode(['error' => 'Record not found']);
    }
} else {
    echo json_encode(['error' => 'Missing evalId parameter']);
}
?>
