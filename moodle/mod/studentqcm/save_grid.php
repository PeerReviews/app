<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");

header("Content-Type: application/json");

// Récupérer les données JSON envoyées par fetch()
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "Aucune donnée reçue"]);
    exit;
}

if (isset($data["gridData"])) {
    $gridData = $data["gridData"]; // Stocke les données reçues

    // Debug : Écrit dans un fichier (tu peux remplacer ça par un stockage en base de données)
    file_put_contents("gridData.json", json_encode($gridData, JSON_PRETTY_PRINT));

    echo json_encode(["status" => "success", "message" => "Données enregistrées"]);
} else {
    echo json_encode(["status" => "error", "message" => "Aucune donnée reçue"]);
}
?>
