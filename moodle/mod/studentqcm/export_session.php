<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);

require_login();

// Vérifier si la session existe
$session = $DB->get_record('studentqcm', ['id' => $session_id], '*', MUST_EXIST);

// Récupération des étudiants
$students = $DB->get_records('students', ['sessionid' => $session->id]);

// Création d'un dossier temporaire pour stocker les fichiers XML
$tmp_dir = sys_get_temp_dir() . '/students_exports_' . uniqid();
mkdir($tmp_dir, 0777, true);

$zip = new ZipArchive();
$zip_file = $tmp_dir . '/students_productions.zip';
$zip->open($zip_file, ZipArchive::CREATE);

foreach ($students as $student) {
    $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" ?><student_production/>');

    $student_name = $DB->get_record('user', array('id' => $student->userid));
    $student_fullname = ucwords(strtolower($student_name->firstname)) . '_' . ucwords(strtolower($student_name->lastname));
    $student_full = ucwords(strtolower($student_name->firstname)) . ' ' . ucwords(strtolower($student_name->lastname));

    $xml->addChild('student_name', $student_full);

    $productions_xml = $xml->addChild('productions');
    $prods = $DB->get_records('studentqcm_question', ['userid' => $student->id]);
    
    foreach ($prods as $prod) {
        $prod_xml = $productions_xml->addChild('production');
        $prod_xml->addChild('question', htmlspecialchars($prod->question, ENT_QUOTES, 'UTF-8'));
        $prod_xml->addChild('global_comment', htmlspecialchars($prod->global_comment, ENT_QUOTES, 'UTF-8'));
        $prod_xml->addChild('context', htmlspecialchars($prod->context, ENT_QUOTES, 'UTF-8'));
        $prod_xml->addChild('competency', htmlspecialchars($prod->competency, ENT_QUOTES, 'UTF-8'));
        $prod_xml->addChild('subcompetency', htmlspecialchars($prod->subcompetency, ENT_QUOTES, 'UTF-8'));
        $prod_xml->addChild('type', htmlspecialchars($prod->type, ENT_QUOTES, 'UTF-8'));

        // Ajouter les fichiers associés
        $files = $DB->get_records('studentqcm_file', ['itemid' => $prod->id]);
        $files_xml = $prod_xml->addChild('files');
        foreach ($files as $file) {
            $file_xml = $files_xml->addChild('file');
            $file_xml->addChild('filename', htmlspecialchars($file->filename, ENT_QUOTES, 'UTF-8'));
            $file_xml->addChild('mimetype', htmlspecialchars($file->mimetype, ENT_QUOTES, 'UTF-8'));
            

            // $fs = get_file_storage(); // Charger le gestionnaire de fichiers
            // $file_record = $fs->get_file($file->contextid, $file->component, $file->filearea, $file->itemid, $file->filepath, $file->filename);
    
            //  // Construire l'URL complète
            // if ($file_record) {
            //     $file_url = moodle_url::make_weburl($file_record->get_url());
            // } else {
            //     $file_url = ''; 
            // }
            // $file_xml->addChild('filepath', htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8'));

        }
    }


    // Ajouter les évaluateurs
    $reviews_xml = $xml->addChild('reviews');
    $eval_prod1 = $DB->get_records('studentqcm_assignedqcm', ['prod1_id' => $student->id]);
    $eval_prod2 = $DB->get_records('studentqcm_assignedqcm', ['prod2_id' => $student->id]);
    $eval_prod3 = $DB->get_records('studentqcm_assignedqcm', ['prod3_id' => $student->id]);
    $evaluators = array_merge(array_values($eval_prod1), array_values($eval_prod2), array_values($eval_prod3));
    
    foreach ($evaluators as $evaluator) {
        $review_name = $DB->get_record('user', array('id' => $evaluator->user_id));
        $review_fullname = ucwords(strtolower($review_name->firstname)) . ' ' . ucwords(strtolower($review_name->lastname));

        $review_xml = $reviews_xml->addChild('review');
        $review_xml->addChild('reviewer_name', $review_fullname);
    }

   $dom = new DOMDocument('1.0', 'UTF-8');
   $dom->preserveWhiteSpace = false;  
   $dom->formatOutput = true;        
   $dom->loadXML($xml->asXML());

   $formatted_xml = $dom->saveXML();

 
   $xml_file = $tmp_dir . '/student_' . $student_fullname. '.xml';
   file_put_contents($xml_file, $formatted_xml);
   $zip->addFile($xml_file, 'student_' . $student_fullname . '.xml');
}

$zip->close();


header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="students_productions.zip"');
header('Content-Length: ' . filesize($zip_file));

readfile($zip_file);

// Nettoyage
array_map('unlink', glob($tmp_dir . '/*.xml'));
rmdir($tmp_dir);
exit;
