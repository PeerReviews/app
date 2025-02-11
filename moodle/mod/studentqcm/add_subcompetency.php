<?php
require_once(__DIR__ . '/../../config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $competency_id = intval($_POST['competency_id']);

    if (!empty($name) && $competency_id > 0) {
        // Création de l'objet à insérer
        $record = new stdClass();
        $record->name = $name;
        $record->competency = $competency_id;
        $record->isCustom = 1; // Indique que c'est une sous-compétence ajoutée manuellement

        // Insérer la sous-compétence dans la base de données
        $new_id = $DB->insert_record('subcompetency', $record);

        // Réponse JSON avec le nouvel ID et le nom de la sous-compétence
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'id' => $new_id, 'name' => $name]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Nom ou compétence manquante.']);
    }
}
?>
