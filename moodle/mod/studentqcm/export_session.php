<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/filelib.php'); 

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);

function convert_html_entities_to_unicode($text) {
    $context = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $context = mb_convert_encoding($context, 'UTF-8', 'UTF-8');

    $decoded_context = preg_replace('/\s+$/u', '', html_entity_decode($context, ENT_QUOTES, 'UTF-8'));

    return strip_tags($decoded_context);
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

$images_dir = $tmp_dir . '/images';
mkdir($images_dir, 0777, true);


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
        $prod_xml->addChild('context', convert_html_entities_to_unicode($prod->context));
        $prod_xml->addChild('competency', htmlspecialchars($prod->competency, ENT_QUOTES, 'UTF-8'));
        $prod_xml->addChild('subcompetency', htmlspecialchars($prod->subcompetency, ENT_QUOTES, 'UTF-8'));
        $prod_xml->addChild('type', htmlspecialchars($prod->type, ENT_QUOTES, 'UTF-8'));

        $context = html_entity_decode($prod->context, ENT_QUOTES, 'UTF-8');
        $pattern = '/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*alt=["\']([^"\']*)["\'](?:\s+width=["\']([^"\']+)["\'])?(?:\s+height=["\']([^"\']+)["\'])?[^>]*>/i';
        
        preg_match_all($pattern, $context, $matches, PREG_SET_ORDER);
        
        $images = [];
        
        foreach ($matches as $match) {
            $images[] = [
                'src' => $match[1],
                'alt' => $match[2],
                'width' => $match[3] ?? null,
                'height' => $match[4] ?? null
            ];
        }
        
        if (!empty($images)) {
            $files_xml = $prod_xml->addChild('files');
            foreach ($images as $image) {
                
                    $image_url = $image['src'];

                    $image_name = basename($image_url);
                    if (strpos($image_url, '../../') === 0) {
                        $image_url = substr($image_url, 6);
                    }

                    $file_record = $DB->get_record('files', ['filepath' => '/' . $image_url]);
                
                    $fs = get_file_storage(); 
                    $file = $fs->get_file_by_id($file_record);
 
                    if ($file) {
     
                            // Ajouter l'image au zip
                            $zip->addFile($images_dir . '/' . $image_name, 'images/' . $image_name);
                    
                            // Ajouter les informations sur le fichier dans le XML
                            $file_xml = $files_xml->addChild('file');
                            $file_xml->addChild('filepath', htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'));
                            $file_xml->addChild('filename', htmlspecialchars($image_name, ENT_QUOTES, 'UTF-8'));

                    } else {
                        // Log si le fichier n'a pas été trouvé dans la table mdl_files
                        error_log("Aucun fichier trouvé pour " . $image_url);
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

            // Ajout les fichiers de answers
            $context = html_entity_decode($answer->answer, ENT_QUOTES, 'UTF-8');
            $pattern = '/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*alt=["\']([^"\']*)["\'](?:\s+width=["\']([^"\']+)["\'])?(?:\s+height=["\']([^"\']+)["\'])?[^>]*>/i';

            preg_match_all($pattern, $context, $matches, PREG_SET_ORDER);
            
            $images = [];
            
            foreach ($matches as $match) {
                $images[] = [
                    'src' => $match[1],
                    'alt' => $match[2],
                    'width' => $match[3] ?? null,
                    'height' => $match[4] ?? null
                ];
            }

            if(!empty($images)) {
                $answer_files_xml = $answer_xml->addChild('answer_files');
                foreach ($images as $image) {
                    $image_url = $image['src'];
                    if (strpos($image_url, '../../') === 0) {
                        $image_url = substr($image_url, 6);
                    }
                    $image_url = $CFG->wwwroot . '/' . ltrim($image_url, '/');
                    
                    $parsed_url = parse_url($image_url);
                    $image_name = basename($parsed_url['path']);

                    // Extraire les informations sur le fichier à partir de la table mdl_files
                    $relative_path = substr($parsed_url['path'], 1); // Enlever le premier '/'
                    $file_record = $DB->get_record('files', ['filepath' => '/' . $relative_path]);
                    if ($file_record) {
                        // Récupérer le chemin physique du fichier dans le système de fichiers Moodle
                        $file_path = $CFG->dataroot . '/filedir/' . substr($file_record->contenthash, 0, 2) . '/' . substr($file_record->contenthash, 2, 2) . '/' . $file_record->contenthash;
                    
                        // Vérifier si le fichier existe et est accessible
                        if (file_exists($file_path)) {
                            // Copier le fichier dans le répertoire des images
                            copy($file_path, $images_dir . '/' . $image_name);
                            
                            // Ajouter l'image au zip
                            $zip->addFile($images_dir . '/' . $image_name, 'images/' . $image_name);
                    
                            // Ajouter les informations sur le fichier dans le XML
                            $file_xml = $files_xml->addChild('file');
                            $file_xml->addChild('filepath', htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'));
                            $file_xml->addChild('filename', htmlspecialchars($image_name, ENT_QUOTES, 'UTF-8'));
                        } else {
                            // Log si le fichier n'a pas été trouvé
                            error_log("Le fichier " . $image_name . " n'a pas pu être trouvé sur le serveur.");
                        }
                    } else {
                        // Log si le fichier n'a pas été trouvé dans la table mdl_files
                        error_log("Aucun fichier trouvé pour " . $image_url);
                    }
                }
            }

            // Ajout les fichiers des explications
            $context = html_entity_decode($answer->explanation, ENT_QUOTES, 'UTF-8');
            $pattern = '/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*alt=["\']([^"\']*)["\'](?:\s+width=["\']([^"\']+)["\'])?(?:\s+height=["\']([^"\']+)["\'])?[^>]*>/i';

            preg_match_all($pattern, $context, $matches, PREG_SET_ORDER);
            
            $images = [];
            
            foreach ($matches as $match) {
                $images[] = [
                    'src' => $match[1],
                    'alt' => $match[2],
                    'width' => $match[3] ?? null,
                    'height' => $match[4] ?? null
                ];
            }

            if(!empty($images)) {
                $explanation_files_xml = $answer_xml->addChild('explanation_files');
                foreach ($images as $image) {
                    $image_url = $image['src'];
                    if (strpos($image_url, '../../') === 0) {
                        $image_url = substr($image_url, 6);
                    }
                    $image_url = $CFG->wwwroot . '/' . ltrim($image_url, '/');
                    
                    $parsed_url = parse_url($image_url);
                    $image_name = basename($parsed_url['path']);

                    // Extraire les informations sur le fichier à partir de la table mdl_files
                    $relative_path = substr($parsed_url['path'], 1); // Enlever le premier '/'
                    $file_record = $DB->get_record('files', ['filepath' => '/' . $relative_path]);
                    if ($file_record) {
                        // Récupérer le chemin physique du fichier dans le système de fichiers Moodle
                        $file_path = $CFG->dataroot . '/filedir/' . substr($file_record->contenthash, 0, 2) . '/' . substr($file_record->contenthash, 2, 2) . '/' . $file_record->contenthash;
                    
                        // Vérifier si le fichier existe et est accessible
                        if (file_exists($file_path)) {
                            // Copier le fichier dans le répertoire des images
                            copy($file_path, $images_dir . '/' . $image_name);
                            
                            // Ajouter l'image au zip
                            $zip->addFile($images_dir . '/' . $image_name, 'images/' . $image_name);
                    
                            // Ajouter les informations sur le fichier dans le XML
                            $file_xml = $files_xml->addChild('file');
                            $file_xml->addChild('filepath', htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'));
                            $file_xml->addChild('filename', htmlspecialchars($image_name, ENT_QUOTES, 'UTF-8'));
                        } else {
                            // Log si le fichier n'a pas été trouvé
                            error_log("Le fichier " . $image_name . " n'a pas pu être trouvé sur le serveur.");
                        }
                    } else {
                        // Log si le fichier n'a pas été trouvé dans la table mdl_files
                        error_log("Aucun fichier trouvé pour " . $image_url);
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

   $images_files = glob($images_dir . '/*'); // Liste des fichiers images
   foreach ($images_files as $file) {
       $zip->addFile($file, 'images/' . basename($file));
   }
   
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
array_map('unlink', glob($images_dir . '/*'));
rmdir($images_dir);
exit;
