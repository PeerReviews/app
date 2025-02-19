<?php
require_once(__DIR__ . '/../../config.php');

require_login();

header('Content-Type: application/json'); // Assurer un retour en JSON

try {
    $cmid = required_param('cmid', PARAM_INT); // Récupération de l'ID du module
    $context = context_module::instance($cmid);
    $fs = get_file_storage();

    // Vérifier la présence d'un fichier
    if (!isset($_FILES['file'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Aucun fichier reçu.']);
        exit;
    }

    $file = $_FILES['file'];
    $filename = time() . "_" . clean_param($file['name'], PARAM_FILE);
    $file_record = [
        'contextid' => $context->id,
        'component' => 'mod_studentqcm',
        'filearea' => 'questionfiles',
        'itemid' => 0,
        'filepath' => '/',
        'filename' => $filename
    ];

    if (!file_exists($file['tmp_name'])) {
        echo json_encode(['error' => 'Fichier temporaire introuvable: ' . $file['tmp_name']]);
        exit;
    }
    
    if (!$context->id) {
        echo json_encode(['error' => 'Context ID invalide']);
        exit;
    }
    
    error_log("Tentative d'enregistrement du fichier: " . print_r($file, true));

    // Enregistrer le fichier dans Moodle
    $stored_file = $fs->create_file_from_pathname($file_record, $file['tmp_name']);

    error_log("Fichier stored: " . print_r($stored_file, true));

    if ($stored_file) {
        // Génération de l'URL du fichier
        $file_url = moodle_url::make_pluginfile_url(
            $context->id, 'mod_studentqcm', 'questionfiles', 0, '/', $filename
        )->out(false);

        echo json_encode(['location' => $file_url]); // TinyMCE attend "location"
        exit;
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Erreur lors de l’enregistrement du fichier.']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Une erreur est survenue.', 'details' => $e->getMessage()]);
    exit;
}
