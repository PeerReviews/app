<?php

// Inclure le fichier de configuration de Moodle pour initialiser l'environnement Moodle
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours et l'ID du QCM depuis l'URL
$id = required_param('id', PARAM_INT);  // ID du cours
$session_id = required_param('session_id', PARAM_INT);  // ID du QCM à supprimer

// Obtenir les informations du module de cours
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$sessionToDuplicate = $DB->get_record('studentqcm', array('id' => $session_id), '*', MUST_EXIST);

// Vérifier que l'utilisateur est connecté et qu'il a les droits nécessaires
require_login($course, true, $cm);


// Préparer les données du QCM
$record_session = new stdClass();

//Data informations du référentiel
$record_session->name = $sessionToDuplicate->name;
$record_session->intro = $sessionToDuplicate->intro['text'];
$record_session->introformat = $sessionToDuplicate->introformat;
$record_session->timecreated = time();
$record_session->timemodified = $record_session->timecreated;
$record_session->date_start_referentiel = $sessionToDuplicate->date_start_referentiel;
$record_session->date_end_referentiel = $sessionToDuplicate->date_end_referentiel;

$referentielToDuplicate = $DB->get_record('referentiel', array('studentqcm_id' => $session_id), '*', MUST_EXIST);

$record_referentiel = new stdClass();
$record_referentiel->name = $referentielToDuplicate->name;
$record_referentiel->studentqcm_id = $session_id;
$referentiel_id = $DB->insert_record('referentiel', $record_referentiel);
$record_session->referentiel = $referentiel_id;

$record_session->start_date_1 = $sessionToDuplicate->start_date_1;
$record_session->end_date_1 = $sessionToDuplicate->end_date_1;
$record_session->end_date_tt_1 = $sessionToDuplicate->end_date_tt_1;

$record_session->start_date_2 = $sessionToDuplicate->start_date_2;
$record_session->end_date_2 = $sessionToDuplicate->end_date_2;
$record_session->end_date_tt_2 = $sessionToDuplicate->end_date_tt_2;

$record_session->start_date_3 = $sessionToDuplicate->start_date_3;
$record_session->end_date_3 = $sessionToDuplicate->end_date_3;
$record_session->end_date_tt_3 = $sessionToDuplicate->end_date_tt_3;


$competencesToDuplicateArray = $DB->get_record('competency', array('referentiel' => $referentielToDuplicate->id), '*', MUST_EXIST);

//Data compétences, sous-compétences, mot-clefs
if (!empty($competencesToDuplicateArray)) {    
    foreach ($competencesToDuplicateArray as $competence) {
        // Insérer la compétence
        $comp_record = new stdClass();
        $comp_record->referentiel = $referentiel_id;
        $comp_record->name = $competence->name;
        $competence_id = $DB->insert_record('competency', $comp_record);

        $subcompetencesToDuplicateArray = $DB->get_record('subcompetency', array('competency' => $competence->id), '*', MUST_EXIST);
        
        // Insérer les sous-compétences
        foreach ($subcompetencesToDuplicateArray as $sub) {
            $subcomp_record = new stdClass();
            $subcomp_record->competency = $competence_id;
            $subcomp_record->name = $sub->name;
            $subcompetence_id = $DB->insert_record('subcompetency', $subcomp_record);

            $keywordToDuplicateArray = $DB->get_record('keyword', array('competency' => $sub->id), '*', MUST_EXIST);

        // Insérer les mots-clés
            foreach ($keywordToDuplicateArray as $keyword) {
                $key_record = new stdClass();
                $key_record->word = $keyword->word;
                $key_record->subcompetency = $subcompetence_id;
                $DB->insert_record('keyword', $key_record);
            }
        }
    }
}
    

//Data questions
$record_session->nbQcm = $sessionToDuplicate->choix_qcm;
$record_session->nbQcu = $sessionToDuplicate->choix_qcu;
$record_session->nbTcs = $sessionToDuplicate->choix_tcs;
$record_session->nbPop = $sessionToDuplicate->choix_pop;

//CHERCHER POP

$popsArray = json_decode($data->pops_data, true);
if (!empty($popsArray)) {
    foreach ($popsArray as $pop) {
        // Insérer un pop
        $pop_record = new stdClass();
        $pop_record->nbqcm = $pop['qcm'];
        $pop_record->nbqcu = $pop['qcu'];
        $pop_record->refId = $referentiel_id;
        $pop_id = $DB->insert_record('question_pop', $pop_record);
    }
}

if (empty($record->name)) {
    throw new moodle_exception('missingfield', 'studentqcm', '', 'name');
}
// echo '<pre>';
// print_r($record);
// echo '</pre>';
// exit;   

// var_dump($record);
// die();

// Insérer l'instance principale dans la table 'studentqcm'
$id = $DB->insert_record('studentqcm', $record);
if (!$id) {
    throw new moodle_exception('insertfailed', 'studentqcm');
}



$record_studentqcm_instance = new stdClass();
$record_studentqcm_instance->name = trim($data->name_plugin);
$DB->insert_record('studentqcm_instance', $record_studentqcm_instance);

// Rediriger vers la liste des QCM avec un message de succès
redirect(new moodle_url('/mod/studentqcm/admin_session_gestion.php', array('id' => $id)), get_string('qcm_deleted', 'mod_studentqcm'), null, \core\output\notification::NOTIFY_SUCCESS);

?>
