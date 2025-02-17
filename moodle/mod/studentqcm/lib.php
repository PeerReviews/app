<?php
/**
 * Lib.php pour le module StudentQCM.
 * Définit les fonctions d'ajout, de mise à jour et de suppression des instances du plugin.
 */

/**
 * Ajouter une nouvelle instance de studentqcm dans la base de données.
 *
 * @param stdClass $data Données du formulaire
 * @param mod_studentqcm_mod_form $mform Formulaire Moodle (optionnel)
 * @return int ID de l'instance ajoutée
 */
function studentqcm_add_instance($data, $mform = null) {
    global $CFG, $DB;

    require_once("$CFG->libdir/resourcelib.php");

    // Initialisation des dates
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;

    $record = new stdClass();

    //Data informations du référentiel
    $record->name = trim($data->name_referentiel);
    $record->intro = isset($data->intro['text']) ? trim($data->intro['text']) : '';
    $record->timecreated = $data->timecreated;
    $record->timemodified = $data->timemodified;
    $record->date_start_referentiel = $data->date_start_referentiel;
    $record->date_end_referentiel = $data->date_end_referentiel;

    //Data compétences, sous-compétences, mot-clefs
    if (!empty($data->competences_data)) {
        $competencesArray = json_decode($data->competences_data, true);
    
        foreach ($competencesArray as $competence) {
            // Insérer la compétence
            $comp_record = new stdClass();
            $comp_record->studentqcm_id = $id;
            $comp_record->competence = trim($competence['name']);
            $competence_id = $DB->insert_record('studentqcm_competency', $comp_record);
    
            // Insérer les sous-compétences
            foreach ($competence['subCompetences'] as $sub) {
                $subcomp_record = new stdClass();
                $subcomp_record->competence_id = $competence_id;
                $subcomp_record->sous_competence = trim($sub['name']);
                $subcompetence_id = $DB->insert_record('studentqcm_subcompetency', $subcomp_record);
    
                // Insérer les mots-clés
                foreach ($sub['keywords'] as $keyword) {
                    $key_record = new stdClass();
                    $key_record->studentqcm_id = $id;
                    $key_record->keyword = trim($keyword);
                    $DB->insert_record('studentqcm_keywords', $key_record);
                }
            }
        }
    }

    foreach (['start_date_1', 'end_date_1', 'end_date_tt_1', 'start_date_2', 'end_date_2', 'end_date_tt_2', 'start_date_3', 'end_date_3', 'end_date_tt_3'] as $date_field) {
        if (isset($data->$date_field)) {
            if (is_int($data->$date_field) && $data->$date_field > 0) {
                $record->$date_field = $data->$date_field;
            } else {
                throw new moodle_exception('invaliddate', 'studentqcm', '', $date_field);
            }
        } else {
            throw new moodle_exception('missingfield', 'studentqcm', '', $date_field);
        }
    }

    $infoEtus = optional_param('hidden_files_etu', '', PARAM_RAW);
    // $infoEtus = json_decode($infoEtus, true);
    echo '<pre>';
    print_r($infoEtus);
    echo '</pre>';
    exit;


    foreach ($infoEtus as $infoEtu) {
        $etu_record = new stdClass();
        $etu_record->name = $infoEtu['name'];
        $etu_record->surname = $infoEtu['surname'];
        $etu_record->mail = $infoEtu['mail'];
        $etudiant -> $DB->get_record('user', array('mail' => $etu_record->mail), '*', MUST_EXIST);
        // if(!$etudiant){
        //     throw new moodle_exception('missingfield', 'studentqcm', '', 'name');
        // } else {
        //     $etu_record->id = $etudiant['id'];
        // }
        $etu_id = $DB->insert_record('studentqcm_tierstemps', $etu_record);
    }

    //Data étudiants
    if (!empty($data->add_etus_data)) {
        $etusArray = json_decode($data->add_etus_data, true);
    
        foreach ($etusArray as $etu) {
            // Insérer la compétence
            $etu_record = new stdClass();
            $etu_record->name = $etu['name'];
            $etu_record->surname = $etu['surname'];
            $etu_record->mail = $etu['mail'];
            $etudiant = $DB->get_record('user', array('mail' => $etu_record->mail), '*', MUST_EXIST);
            // if(!$etudiant){
            //     throw new moodle_exception('missingfield', 'studentqcm', '', 'name');
            // } else {
            //     $etu_record->id = $etudiant['id'];
            // }
            $etu_id = $DB->insert_record('studentqcm_tierstemps', $etu_record);
        }
    }

    $infoProfs = optional_param('uploaded_files_etu', '', PARAM_RAW);
    $infoProfs = json_decode($infoProfs, true);

    foreach ($infoProfs as $infoProf) {
        $prof_record = new stdClass();
        $prof_record->name = $infoProf['name'];
        $prof_record->surname = $infoProf['surname'];
        $prof_record->mail = $infoProf['mail'];
        $prof = $DB->get_record('user', array('mail' => $prof_record->mail), '*', MUST_EXIST);
        // if(!$prof){
        //     throw new moodle_exception('missingfield', 'studentqcm', '', 'name');
        // } else {
        //     $prof_record->id = $prof['id'];
        // }
        $prof_id = $DB->insert_record('studentqcm_prof', $prof_record);
    }

    //Data profs
    // if (!empty($data->add_profs_data)) {
    //     $profsArray = json_decode($data->add_profs_data, true);
    
    //     foreach ($profsArray as $infoProf) {
    //         // Insérer la compétence
    //         $prof_record = new stdClass();
    //         $prof_record->name = $infoProf['name'];
    //         $prof_record->surname = $infoProf['surname'];
    //         $prof_record->mail = $infoProf['mail'];
    //         $prof -> $DB->get_record('user', array('mail' => $prof_record->mail), '*', MUST_EXIST);
    //         // if(!$prof){
    //         //     throw new moodle_exception('missingfield', 'studentqcm', '', 'name');
    //         // } else {
    //         //     $prof_record->id = $prof['id'];
    //         // }
    //         $prof_id = $DB->insert_record('studentqcm_prof', $prof_record);

    //     }
    // }

    //Data questions
    $record->nb_qcm = $data->nb_qcm;
    $record->nb_qcu = $data->nb_qcu;
    $record->nb_tcs = $data->nb_tcs;
    $record->nb_pop = $data->nb_pop;

    if (empty($record->name_referentiel)) {
        throw new moodle_exception('missingfield', 'studentqcm', '', 'name');
    }
    if (empty($record->intro)) {
        throw new moodle_exception('missingfield', 'studentqcm', '', 'intro');
    }

    $id = $DB->insert_record('studentqcm', $record);
    if (!$id) {
        throw new moodle_exception('insertfailed', 'studentqcm');
    }

    $nb_pops = $data->choix_pop;
    if (!empty($nb_pops)) {
    
        for ($i=0; $i<$nb_pops; $i++) {
            // Insérer un pop
            $pop_record = new stdClass();
            $pop_record->$nbqcm = $data->{"nb_qcm$i"};
            $pop_record->$nbqcu = $data->{"nb_qcu$i"};
            $pop_record->$refId = $id;
            $pop_id = $DB->insert_record('studentqcm_pop', $pop_record);
        }
    }
    

    return $id;
}




/**
 * Mettre à jour une instance existante de studentqcm dans la base de données.
 *
 * @param object $data Données mises à jour du formulaire
 * @param object|null $mform Formulaire Moodle (optionnel)
 * @return bool Succès de l'opération
 */
function studentqcm_update_instance($data, $mform = null) {
    global $DB;

    // Ajouter la date de modification.
    $data->timemodified = time();

    // L'ID de l'instance est nécessaire pour la mise à jour.
    $data->id = $data->instance;

    // Vérification des champs obligatoires.
    if (!isset($data->name) || empty(trim($data->name))) {
        throw new moodle_exception('missingfield', 'studentqcm', '', 'name');
    }
    if (!isset($data->intro) || empty(trim($data->intro))) {
        throw new moodle_exception('missingfield', 'studentqcm', '', 'intro');
    }

    // Mise à jour des données dans la table studentqcm.
    return $DB->update_record('studentqcm', $data);
}

/**
 * Supprimer une instance de studentqcm de la base de données.
 *
 * @param int $id ID de l'instance à supprimer
 * @return bool Succès de l'opération
 */
function studentqcm_delete_instance($id) {
    global $DB;

    // Vérifier si l'instance existe.
    if (!$DB->record_exists('studentqcm', array('id' => $id))) {
        throw new moodle_exception('invalidinstance', 'studentqcm');
    }

    // Supprimer l'instance de la table studentqcm.
    return $DB->delete_records('studentqcm', array('id' => $id));
}

/**
 * Retourne une liste de fonctions de rappel pour les hooks du plugin.
 * Utilisé par Moodle dans différents contextes (e.g., backups, resets, etc.).
 *
 * @return array Liste des callbacks disponibles
 */
function studentqcm_get_callbacks() {
    return [
        'backup' => 'studentqcm_backup_instance',
        'reset'  => 'studentqcm_reset_instance',
    ];
}

// function studentqcm_get_capabilities() {
//     return array(
//         'mod/studentqcm:addinstance' => array(
//             'captype' => 'write',
//             'contextlevel' => CONTEXT_MODULE,
//             'legacy' => array(
//                 'guest' => CAP_PREVENT,
//                 'student' => CAP_PREVENT,
//                 'teacher' => CAP_ALLOW,
//                 'editingteacher' => CAP_ALLOW,
//                 'manager' => CAP_ALLOW
//             ),
//         ),
//     );
// }





