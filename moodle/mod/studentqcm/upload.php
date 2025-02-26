<?php
require_once('../../config.php');
require_login();

$contextid = context_system::instance()->id;
$fs = get_file_storage();

// Vérifier si un fichier a été uploadé
if (!isset($_FILES['file'])) {
    echo json_encode(['error' => 'Aucun fichier reçu.']);
    exit;
}

$file = $_FILES['file'];

// Définir la zone de fichier pour Moodle
$file_record = [
    'contextid' => $contextid,
    'component' => 'mod_studentqcm',
    'filearea' => 'questionfiles',
    'itemid' => 0,
    'filepath' => '/',
    'filename' => $file['name']
];

// Supprime les fichiers dupliqués
if ($fs->file_exists($contextid, 'mod_studentqcm', 'questionfiles', 0, '/', $file['name'])) {
    $fs->delete_area_files($contextid, 'mod_studentqcm', 'questionfiles');
}

// Enregistrer le fichier dans Moodle
$stored_file = $fs->create_file_from_pathname($file_record, $file['tmp_name']);

if ($stored_file) {
    echo json_encode(['location' => moodle_url::make_pluginfile_url(
        $contextid, 'mod_studentqcm', 'questionfiles', 0, '/', $file['name']
    )->out(false)]);
} else {
    echo json_encode(['error' => 'Erreur lors de l’enregistrement du fichier.']);
}

?>
