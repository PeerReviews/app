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
    global $CFG, $DB, $USER;

    require_once("$CFG->libdir/resourcelib.php");

    // Initialisation des dates
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;

    $session = $DB->get_record('studentqcm', ['archived' => 0], '*', MUST_EXIST);

    // Préparer les données du QCM
    $record = new stdClass();

    //Data informations du référentiel
    $record->name = trim($data->name_plugin);
    $record->intro = isset($data->intro['text']) ? trim($data->intro['text']) : '';
    $record->introformat = isset($data->intro['format']) ? $data->intro['format'] : 0;
    $record->timecreated = $data->timecreated;
    $record->timemodified = $data->timemodified;
    $record->date_start_referentiel = $data->date_start_referentiel;
    $record->date_end_referentiel = $data->date_end_referentiel;

    $record_referentiel = new stdClass();
    $record_referentiel->name = trim($data->name_referentiel);
    $record_referentiel->sessionid = $session->id;
    $referentiel_id = $DB->insert_record('referentiel', $record_referentiel);
    $record->referentiel = $referentiel_id;

    // Validation des dates
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

    //Data compétences, sous-compétences, mot-clefs
    if (!empty($data->competences_data) || $data->competences_data == "[]") {
        $competencesArray = json_decode($data->competences_data, true);
        
        foreach ($competencesArray as $competence) {
            // Insérer la compétence
            $comp_record = new stdClass();
            $comp_record->referentiel = $referentiel_id;
            $comp_record->name = trim($competence['name']);
            $comp_record->sessionid = $session->id;
            $competence_id = $DB->insert_record('competency', $comp_record);
    
            // Insérer les sous-compétences
            foreach ($competence['subCompetences'] as $sub) {
                $subcomp_record = new stdClass();
                $subcomp_record->competency = $competence_id;
                $subcomp_record->name = trim($sub['name']);
                $subcomp_record->sessionid = $session->id;
                $subcompetence_id = $DB->insert_record('subcompetency', $subcomp_record);
    
                // Insérer les mots-clés
                foreach ($sub['keywords'] as $keyword) {
                    $key_record = new stdClass();
                    $key_record->word = trim($keyword);
                    $key_record->subcompetency = $subcompetence_id;
                    $key_record->sessionid = $session->id;
                    $DB->insert_record('keyword', $key_record);
                }
            }
        }
    } else {
        throw new moodle_exception('invaliddate', 'studentqcm', '', $competences_data);
    }
        
    // Data questions
    $record->nbQcm = $data->choix_qcm;
    $record->nbQcu = $data->choix_qcu;
    $record->nbTcs = $data->choix_tcs;
    $record->nbPop = $data->choix_pop;

    $popsArray = json_decode($data->pops_data, true);
    if (!empty($popsArray)) {
        foreach ($popsArray as $pop) {
            // Insérer un pop
            $pop_record = new stdClass();
            $pop_record->nbqcm = $pop['qcm'];
            $pop_record->nbqcu = $pop['qcu'];
            $pop_record->sessionid = $session->id;
            $pop_id = $DB->insert_record('question_pop', $pop_record);
        }
    }

    // Course files

    if (!empty($data->selectedCourse)) {
        $selectedCourses = json_decode($data->selectedCourse, true);
        
        foreach($selectedCourses as $fileId => $file){
            $file_record = $DB->get_record('studentqcm_file', ['filearea' => 'coursefiles', 'filename' => $file['file_name']]);
            $competency = $DB->get_record('competency', ['name' => $file['competency_name'], 'sessionid' => $session->id]);
            
            $file_record->id_referentiel = $referentiel_id;
            $file_record->id_competency = $competency->id;
            $file_id = $DB->update_record('studentqcm_file', $file_record);
        }
    }

    if (empty($record->name)) {
        throw new moodle_exception('missingfield', 'studentqcm', '', 'name');
    }

    // Insérer l'instance principale dans la table 'studentqcm'
    $id = $DB->insert_record('studentqcm', $record);
    if (!$id) {
        throw new moodle_exception('insertfailed', 'studentqcm');
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

function getImageFile($contextid, $component, $filearea, $itemid, $filename) {
    $fs = get_file_storage();
    // Récupérer tous les fichiers pour le contexte, le composant, l'élément et la zone spécifiés
    $file_records = $fs->get_area_files(
        $contextid,
        $component,
        $filearea,
        abs($itemid),
        'sortorder', // ou un autre critère de tri
        false // Inclure les fichiers supprimés ou non
    );
    
    // Parcourir les fichiers et filtrer par user_id et filename
    foreach ($file_records as $file) {
        if ($file->get_filename() === $filename) {
            return $file;  // Retourner le fichier correspondant
        }
    }
    
    return null;  // Retourner null si aucun fichier trouvé
}

function mod_studentqcm_pluginfile(
    $course,
    $cm,
    $context,
    string $filearea,
    array $args,
    bool $forcedownload
): bool {
    global $DB, $USER;

    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    // if ($context->contextlevel != CONTEXT_MODULE) {
    //     return false;
    // }
    
    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'contextfiles' && $filearea !== 'answerfiles' && $filearea !== 'explanationfiles' && $filearea !== 'coursefiles') {
        return false;
    }

    // Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
    require_login($course, true, $cm);

    // Check the relevant capabilities - these may vary depending on the filearea being accessed.
    if (!has_capability('mod/studentqcm:view', $context)) {
        return false;
    }

    // The args is an array containing [itemid, path].
    // Fetch the itemid from the path.
    $itemid = array_shift($args);

    // For a plugin which does not specify the itemid, you may want to use the following to keep your code consistent:
    // $itemid = null;

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.
    if (empty($args)) {
        // $args is empty => the path is '/'.
        $filepath = '/';
        debugging($filepath, DEBUG_DEVELOPER);
    } else {
        // $args contains the remaining elements of the filepath.
        $filepath = '/' . implode('/', $args) . '/';
        debugging($filepath, DEBUG_DEVELOPER);
    }

    // Retrieve the file from the Files API.
    $systemcontext = context_system::instance();
    $fs = get_file_storage();

    $file = getImageFile($context->id, 'mod_studentqcm', $filearea, $itemid, $filename);
    if (!$file) {
        throw new moodle_exception("Le fichier est introuvable !" . " -- " . $filepath . " -- " . $filearea . ' error : ' . $filename);
        // The file does not exist.
        return false;
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 0, 0, $forcedownload);

    // Return true to indicate that the file has been served.
    return true;
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





