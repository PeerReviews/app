<?php
require_once(__DIR__ . '/../../config.php');

require_login();

header('Content-Type: application/json'); // Assurer un retour en JSON

try{ 
    $cmid = required_param('cmid', PARAM_INT); // Récupération de l'ID du module
    $filearea = required_param('filearea', PARAM_TEXT);
    $itemid = required_param('itemid', PARAM_RAW);
    $itemid = intval($itemid);

    $id_referentiel = optional_param('id_referentiel', null, PARAM_INT);
    $id_competency = optional_param('id_competency', null, PARAM_INT);
    
    $context = context_system::instance();
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
        'filearea' => $filearea,
        'itemid' => abs($itemid),
        'filepath' => '/',
        'filename' => $filename,
        'userid' => $USER->id, 
    ];
    

    if (!file_exists($file['tmp_name'])) {
        echo json_encode(['error' => 'Fichier temporaire introuvable: ' . $file['tmp_name']]);
        exit;
    }
    
    if (!$context->id) {
        echo json_encode(['error' => 'Context ID invalide']);
        exit;
    }
    
    error_log("Tentative d'enregistrement du fichier : " . print_r($file, true));

    // Enregistrer le fichier dans Moodle
    try {
        $stored_file = $fs->create_file_from_pathname($file_record, $file['tmp_name']);
        
        if ($itemid <= 0){
            $file_id = $stored_file->get_id(); 
            $DB->set_field('files', 'referencefileid', 0, array('id' => $file_id));
        }
    }
    catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Stored file', 'details' => $e->getMessage()]);
        exit;
    }
    error_log("Fichier stored : " . print_r($stored_file, true));

    if ($stored_file) {
        // Enregistrement en BD du fichier 
        $file_record = new stdClass();
        $file_record->itemid = $itemid;
        $file_record->userid = $USER->id;
        $file_record->filearea = $filearea;
        $file_record->mimetype = $stored_file->get_mimetype();
        if ($id_referentiel !== null && $id_competency !== null) {
            $file_record->id_referentiel = $id_referentiel;
            $file_record->id_competency = $id_competency;
            $file_record->iscourse = 1;
        }
        else {
            $file_record->iscourse = 0;
        }

        try {
        $file_record->id = $DB->insert_record('studentqcm_file', $file_record);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Stored file in studentqcm_file', 'details' => $e->getMessage()]);
            exit;
        }
        // Génération de l'URL du fichier
        $file_url = moodle_url::make_pluginfile_url(
            $context->id, 'mod_studentqcm', $filearea, $itemid, '/', $filename
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
