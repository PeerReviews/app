<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);

function convert_html_entities_to_unicode($text) {
    return mb_convert_encoding($text, 'UTF-8', 'HTML-ENTITIES');
}

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
    $xml->addChild('student_id', $student->id);
    $xml->addChild('student_userid', $student->userid);

    $productions_xml = $xml->addChild('productions');
    $prods = $DB->get_records('studentqcm_question', ['userid' => $student->userid]);
    
    foreach ($prods as $prod) {
        $prod_xml = $productions_xml->addChild('production');
        $prod_xml->addChild('question', htmlspecialchars($prod->question, ENT_QUOTES, 'UTF-8'));
        $prod_xml->addChild('global_comment', htmlspecialchars($prod->global_comment, ENT_QUOTES, 'UTF-8'));

        $context = html_entity_decode($prod->context, ENT_QUOTES, 'UTF-8');
        $context = mb_convert_encoding($context, 'UTF-8', 'UTF-8');
        
        $prod_xml->addChild('context', $context);
        $prod_xml->addChild('competency', htmlspecialchars($prod->competency, ENT_QUOTES, 'UTF-8'));
        $prod_xml->addChild('subcompetency', htmlspecialchars($prod->subcompetency, ENT_QUOTES, 'UTF-8'));
        $prod_xml->addChild('type', htmlspecialchars($prod->type, ENT_QUOTES, 'UTF-8'));

        // Ajouter les fichiers des context
        $files = $DB->get_records_sql(
            'SELECT * FROM {studentqcm_file} WHERE itemid = :itemid AND LOWER(filearea) = LOWER(:filearea)',
            ['itemid' => $prod->id, 'filearea' => 'contextfiles']
        );


        if (is_array($files) && count($files) > 0) {
            $files_xml = $answer_xml->addChild('files');
            foreach ($files as $file) {
                $file_xml = $files_xml->addChild('file');
                $file_xml->addChild('filename', htmlspecialchars($file->filename, ENT_QUOTES, 'UTF-8'));
                $file_xml->addChild('mimetype', htmlspecialchars($file->mimetype, ENT_QUOTES, 'UTF-8'));
                
                // Construire l'URL complète pour chaque fichier
                $file_info = $DB->get_record('files', [
                    'filearea' => $file->filearea,
                    'filename' => $file->filename
                ]);

                if ($file_info) {
                    $file_url = moodle_url::make_weburl($CFG->wwwroot . '/pluginfile.php', [
                        'contextid' => $file_info->contextid,
                        'component' => $file_info->component,
                        'filearea' => $file_info->filearea,
                        'itemid' => $file_info->itemid,
                        'filepath' => $file_info->filepath,
                        'filename' => $file_info->filename
                    ]);
                } else {
                    $file_url = ''; 
                }

                $file_xml->addChild('filepath', htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8'));
                if(!empty($file_url)){
                    $file_content = file_get_contents($file_url);
                    $zip_files->addFromString($file->filename, $file_content);
                }
            }
        }


        // Récupérer les réponses pour la question actuelle
        $answers = $DB->get_records('studentqcm_answer', ['question_id' => $prod->id]);
  
        $answers_xml = $prod_xml->addChild('answers');

        foreach ($answers as $answer) {
            $answer_xml = $answers_xml->addChild('answer');

            $answertext = convert_html_entities_to_unicode($answer->answer); 
            $answer_xml->addChild('answer', $answertext);

            $explanation = convert_html_entities_to_unicode($answer->explanation); 
            $answer_xml->addChild('explanation', $explanation);

            $answer_xml->addChild('istrue', $answer->istrue ? 'true' : 'false');
            $answer_xml->addChild('indexation', $answer->indexation);

            // Ajouter les fichiers associés à la réponse
            $filesanswers = $DB->get_records_sql(
                'SELECT * FROM {studentqcm_file} WHERE itemid = :itemid AND LOWER(filearea) = LOWER(:filearea)',
                ['itemid' => $answer->id, 'filearea' => 'answerfiles']
            );

            if (is_array($filesanswers) && count($filesanswers) > 0) {
                $answer_files_xml = $answer_xml->addChild('answer_files');
                foreach ($filesanswers as $file) {
                    $file_xml = $answer_files_xml->addChild('file');
                    $file_xml->addChild('filename', htmlspecialchars($file->filename, ENT_QUOTES, 'UTF-8'));
                    $file_xml->addChild('mimetype', htmlspecialchars($file->mimetype, ENT_QUOTES, 'UTF-8'));
                    
                    // Construire l'URL complète pour chaque fichier
                    $file_info = $DB->get_record('files', [
                        'filearea' => $file->filearea,
                        'filename' => $file->filename
                    ]);

                    if ($file_info) {
                        $file_url = moodle_url::make_weburl($CFG->wwwroot . '/pluginfile.php', [
                            'contextid' => $file_info->contextid,
                            'component' => $file_info->component,
                            'filearea' => $file_info->filearea,
                            'itemid' => $file_info->itemid,
                            'filepath' => $file_info->filepath,
                            'filename' => $file_info->filename
                        ]);
                    } else {
                        $file_url = ''; 
                    }

                    $file_xml->addChild('filepath', htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8'));
                    if(!empty($file_url)){
                        $file_content = file_get_contents($file_url);
                        $zip_files->addFromString($file->filename, $file_content);
                    }
                }
            }

            // Ajouter les fichiers des explications
            $filesexplanation = $DB->get_records_sql(
                'SELECT * FROM {studentqcm_file} WHERE itemid = :itemid AND LOWER(filearea) = LOWER(:filearea)',
                ['itemid' => $answer->id, 'filearea' => 'explanationfiles']
            );


            if (is_array($filesexplanation) && count($filesexplanation) > 0) {
                $explanation_files_xml = $answer_xml->addChild('explanation_files');
                foreach ($filesexplanation as $file) {
                    $file_xml = $explanation_files_xml->addChild('file');
                    $file_xml->addChild('filename', htmlspecialchars($file->filename, ENT_QUOTES, 'UTF-8'));
                    $file_xml->addChild('mimetype', htmlspecialchars($file->mimetype, ENT_QUOTES, 'UTF-8'));
                    
                    // Construire l'URL complète pour chaque fichier
                    $file_info = $DB->get_record('files', [
                        'filearea' => $file->filearea,
                        'filename' => $file->filename
                    ]);

                    if ($file_info) {
                        $file_url = moodle_url::make_weburl($CFG->wwwroot . '/pluginfile.php', [
                            'contextid' => $file_info->contextid,
                            'component' => $file_info->component,
                            'filearea' => $file_info->filearea,
                            'itemid' => $file_info->itemid,
                            'filepath' => $file_info->filepath,
                            'filename' => $file_info->filename
                        ]);
                    } else {
                        $file_url = ''; 
                    }

                    $file_xml->addChild('filepath', htmlspecialchars($file_url, ENT_QUOTES, 'UTF-8'));
                    if(!empty($file_url)){
                        $file_content = file_get_contents($file_url);
                        $zip_files->addFromString($file->filename, $file_content);
                    }
                }
            }
        }

    }


    // Ajouter les évaluateurs
    $reviews_xml = $xml->addChild('reviews');
    $eval_prod1 = $DB->get_records('studentqcm_assignedqcm', ['prod1_id' => $student->userid]);
    $eval_prod2 = $DB->get_records('studentqcm_assignedqcm', ['prod2_id' => $student->userid]);
    $eval_prod3 = $DB->get_records('studentqcm_assignedqcm', ['prod3_id' => $student->userid]);
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
