<?php

require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours
$id = required_param('id', PARAM_INT);
$type = required_param('type', PARAM_TEXT);
$pop_type_id = optional_param('pop_type_id', 0, PARAM_INT);

// Récupérer les informations du module et vérifier l'accès
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);
require_login($course, true, $cm);

$session = $DB->get_record('studentqcm', ['archived' => 0], '*', MUST_EXIST);

$context = context_system::instance();

// Vérifier que la requête est bien un POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $questions = $_POST['questions'];


    // Correction des itemid temporaire dans mdl_files et mdl_studentqcm_file
    $fileareas = ['contextfiles', 'answerfiles', 'explanationfiles'];
    $files = [];

    foreach ($fileareas as $area) {
        $sql = "SELECT * FROM {studentqcm_file} 
                WHERE userid = :userid 
                AND filearea = :filearea 
                AND itemid <= 0";
        
        $params = [
            'userid' => $USER->id,
            'filearea' => $area
        ];

        $files[$area] = $DB->get_records_sql($sql, $params);
    }

    foreach ($questions as $q_id => $question) {
        // Vérifier que la question a bien un texte
        if (!empty(trim($question['question']))) {
            // Préparer l'enregistrement de la question
            $question_record = new stdClass();
            $question_record->userid = $USER->id;
            $question_record->sessionid = $session->id;
            
            $question_record->question = (!empty($question['question'])) 
            ? clean_param($question['question'], PARAM_TEXT) 
            : null;

            $question_record->global_comment = (!empty($question['global_comment'])) 
            ? clean_param($question['global_comment'], PARAM_TEXT) 
            : null;
            
            $question_record->context = (!empty($question['context'])) 
            ? clean_param($question['context'], PARAM_RAW) 
            : null;

            $question_record->referentiel = (!empty($question['referentiel']) && $question['referentiel'] > 0) 
            ? clean_param($question['referentiel'], PARAM_INT) 
            : null;
            $question_record->competency = (!empty($question['competency']) && $question['competency'] > 0) 
            ? clean_param($question['competency'], PARAM_INT) 
            : null;
            $question_record->subcompetency = (!empty($question['subcompetency']) && $question['subcompetency'] > 0) 
            ? clean_param($question['subcompetency'], PARAM_INT) 
            : null;

            $question_record->type = $type;
            
            $question_record->status = isset($_POST['submit']) ? 1 : 0;


            // Ajout dans mdl_studentqcm_pop
            if ($pop_type_id !== 0){

                $pop_record = new stdClass();
                $pop_record->userId = $USER->id;
                $pop_record->popTypeId = $pop_type_id;
                $pop_record->sessionid = $session->id;
 
                $pop_id = $DB->insert_record('studentqcm_pop', $pop_record);
                if (!$pop_id) {
                    throw new moodle_exception('insertfailed', 'studentqcm_pop');
                }
                
                $question_record->popTypeId = $pop_type_id;
                $question_record->isPop = ($pop_type_id !== 0) ? 1 : 0;
                $question_record->popId = $pop_id;

            }

            // Insérer la question et récupérer son ID
            $question_id = $DB->insert_record('studentqcm_question', $question_record);

            if (!$question_id) {
                throw new moodle_exception('insertfailed', 'studentqcm_question');
            }

            // Correction des itemid temporaire dans mdl_files et mdl_studentqcm_file   
            $area = 'contextfiles';
            if (!empty($files[$area])) {
                // Récupérer tous les fichiers de mdl_files en une seule requête
                $sql = "SELECT * FROM {files} 
                        WHERE userid = :userid 
                        AND itemid = 0
                        AND referencefileid = 0
                        AND filearea = :filearea";
            
                $params = [
                    'userid' => $USER->id, // L'utilisateur courant
                    'filearea' => $area
                ];
            
                $mdl_files = $DB->get_records_sql($sql, $params);
            
                // Parcourir les fichiers contextfiles pour mettre à jour studentqcm_file
                foreach ($files[$area] as $file) {
                    $file->itemid = $question_id;
                    $DB->update_record('studentqcm_file', $file);
                }
            
                // Mettre à jour les fichiers de mdl_files en dehors de la boucle précédente
                foreach ($mdl_files as $mdl_file) {
                    $mdl_file->itemid = $question_id;
                    $mdl_file->referencefileid = null;
                    $DB->update_record('files', $mdl_file);

                    // Générer l'URL de fichier
                    $filename = $mdl_file->filename;
                    $real_url = moodle_url::make_pluginfile_url($context->id, 'mod_studentqcm', 'contextfiles', $mdl_file->itemid, '/', $filename)->out();

                    // Mettre à jour le champ context de la question
                    $question_record->context = preg_replace_callback('/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i', function($matches) use ($filename, $real_url) {
                        if (strpos($matches[1], $filename) !== false) {
                            return "<img src='{$real_url}' alt='{$filename}' />";
                        }
                        return $matches[0];  // Sinon, garder la balise telle quelle
                    }, $question_record->context);
                }
            }
            
            $question_record->id = $question_id; // Assurez-vous de définir l'ID pour que la mise à jour affecte l'enregistrement existant
            $updated = $DB->update_record('studentqcm_question', $question_record);
            if (!$updated) {
                throw new moodle_exception('Error updating question: ' . print_r($question_record, true));
            }

            $indexation = 1;
            if (!empty($question['answers'])) {
                foreach ($question['answers'] as $answer) {
                    
                    $answer_record = new stdClass();
                    $answer_record->question_id = $question_id;
                    $answer_record->indexation = $indexation;
                    $answer_record->answer = !empty($answer['answer']) ? clean_param($answer['answer'], PARAM_RAW) : null;
                    $answer_record->explanation = !empty($answer['explanation']) ? clean_param($answer['explanation'], PARAM_RAW) : null;
                    $answer_record->isTrue = isset($answer['correct']) && $answer['correct'] == '1' ? 1 : 0;

                    try {
                        $inserted_answer_id = $DB->insert_record('studentqcm_answer', $answer_record);
                        if (!$inserted_answer_id) {
                            throw new moodle_exception('Error inserting answer: ' . print_r($answer_record, true));
                        }
                    } catch (Exception $e) {
                        debugging("Error inserting answer: " . $e->getMessage());
                        throw $e;
                    }

                    // Correction des itemid temporaire dans mdl_files et mdl_studentqcm_file   
                    $fileareas = ['answerfiles', 'explanationfiles'];

                    foreach ($fileareas as $area) {
                        if (!empty($files[$area])) {
                            // Récupérer tous les fichiers de mdl_files en une seule requête pour chaque filearea
                            $sql = "SELECT * FROM {files} 
                                    WHERE userid = :userid 
                                    AND itemid BETWEEN 1 AND 5 
                                    AND referencefileid = 0
                                    AND filearea = :filearea";
                        
                            $params = [
                                'userid' => $USER->id, // L'utilisateur courant
                                'filearea' => $area
                            ];
                        
                            $mdl_files = $DB->get_records_sql($sql, $params);
                        
                            // Mettre à jour les fichiers de mdl_studentqcm_file
                            foreach ($files[$area] as $file) {
                                // Si l'itemid correspond à $indexation, on effectue la mise à jour

                                if (abs($file->itemid) === $indexation) {
                                    $file->itemid = $inserted_answer_id;
                                    $DB->update_record('studentqcm_file', $file);
                                }
                            }

                            
                        
                            // Mettre à jour les fichiers de mdl_files
                            foreach ($mdl_files as $mdl_file) {
                                // Si l'itemid correspond à $indexation, on effectue la mise à jour

                                if (intval($mdl_file->itemid) === $indexation) {
                                    $mdl_file->itemid = $inserted_answer_id;
                                    $mdl_file->referencefileid = null;
                                    $existingid = $DB->update_record('files', $mdl_file);
                                    if (!$existingid) {
                                        throw new moodle_exception('Error updating files: ' . print_r($mdl_file, true));
                                    }

                                    $filename = $mdl_file->filename;
                                    $real_url = moodle_url::make_pluginfile_url($context->id, 'mod_studentqcm', $area, $mdl_file->itemid, '/', $filename)->out();

                                    if ($area === 'answerfiles') {
                                        // Utilisation de preg_replace_callback pour remplacer les balises <img>
                                        $answer_record->answer = preg_replace_callback('/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i', function($matches) use ($filename, $real_url) {
                                            if (strpos($matches[1], $filename) !== false) {
                                                return "<img src='{$real_url}' alt='{$filename}' />";
                                            }
                                            return $matches[0];  // Sinon, garder la balise telle quelle
                                        }, $answer_record->answer);
                                    } else {
                                        // Utilisation de preg_replace_callback pour remplacer les balises <img>
                                        $answer_record->explanation = preg_replace_callback('/<img\s+[^>]*src=["\']([^"\']+)["\'][^>]*>/i', function($matches) use ($filename, $real_url) {
                                            if (strpos($matches[1], $filename) !== false) {
                                                return "<img src='{$real_url}' alt='{$filename}' />";
                                            }
                                            return $matches[0];  // Sinon, garder la balise telle quelle
                                        }, $answer_record->explanation);
                                    }
                                    
                                }
                            }
                        }
                    }

                    $answer_record->id = $inserted_answer_id; // Assurez-vous de définir l'ID pour que la mise à jour affecte l'enregistrement existant
                    $updated = $DB->update_record('studentqcm_answer', $answer_record);
                    if (!$updated) {
                        throw new moodle_exception('Error updating answer: ' . print_r($answer_record, true));
                    }
                    
                    $indexation++;
                }
            }


            // Vérifier et insérer les mots-clés
            if (!empty($question['keywords']) && is_array($question['keywords'])) {
                foreach ($question['keywords'] as $keyword_id) {
                    if (!empty(trim($keyword_id))) {
                        // Vérifier si la relation existe déjà dans mdl_question_keywords
                        $existing_relation = $DB->get_record('question_keywords', array(
                            'question_id' => $question_id,
                            'keyword_id' => $keyword_id
                        ));
            
                        if (!$existing_relation) {
                            // Préparer les données pour l'insertion
                            $relation_record = new stdClass();
                            $relation_record->question_id = $question_id;
                            $relation_record->keyword_id = $keyword_id;
            
                            // Insérer la relation dans la table
                            try {
                                $DB->insert_record('question_keywords', $relation_record);
                            } catch (Exception $e) {
                                debugging("Erreur lors de l'insertion du mot-clé : " . $e->getMessage());
                            }
                        } else {
                            debugging("Relation déjà existante pour question_id=$question_id et keyword_id=$keyword_id");
                        }
                    }
                }
            }
            
        }
    }

   
}

if (isset($_POST['submit'])) {
    redirect(new moodle_url('/mod/studentqcm/qcm_list.php', array('id' => $id)), get_string('qcm_saved', 'mod_studentqcm'), 2);
}

// Si la requête n'est pas un POST, rediriger
redirect(new moodle_url('/mod/studentqcm/qcm_list.php', array('id' => $id)));
