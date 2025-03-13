<?php
// Inclure les fichiers Moodle nécessaires
define('CLI_SCRIPT', true); // Indique à Moodle que ce script est exécuté en CLI
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/filelib.php');


// ID de contexte où les fichiers sont stockés (par exemple, le contexte du cours ou de l'utilisateur)
$contextid = 1;

// Accéder au stockage des fichiers de Moodle
$fs = get_file_storage();

// Récupérer tous les fichiers associés à l'utilisateur dans le contexte spécifié
$file_records = $fs->get_area_files(
    $contextid,  // ID du contexte (par exemple, contexte du cours, de l'utilisateur)
    'mod_peerreview', // Nom du composant
    'answerfiles',         // Aucune area spécifiée (récupère tous les fichiers associés à l'utilisateur)
    2,      // ItemId
    'sortorder',  // Tri par ordre de classement
    false         // Ne pas inclure les fichiers supprimés
);

// Vérifier si des fichiers existent pour cet utilisateur dans ce contexte
if ($file_records) {
    foreach ($file_records as $file) {
        // Supprimer chaque fichier du système de fichiers Moodle
        $file->delete();
    }

    echo "Tous les fichiers de l'utilisateur ont été supprimés avec succès.";
} else {
    echo "Aucun fichier trouvé pour cet utilisateur dans ce contexte.";
}

?>
