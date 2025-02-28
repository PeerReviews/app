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

    // Préparer les données du QCM
    $record = new stdClass();
    $record->name = trim($data->name);
    $record->intro = isset($data->intro['text']) ? trim($data->intro['text']) : '';
    $record->introformat = isset($data->intro['format']) ? $data->intro['format'] : 0;
    $record->timecreated = $data->timecreated;
    $record->timemodified = $data->timemodified;

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

    // // 1. Ajouter un référentiel si nécessaire
    // if (isset($data->referentiel_name) && !record_exists('mdl_referentiel', 'name', $data->referentiel_name)) {
    //     $referentiel = new stdClass();
    //     $referentiel->name = $data->referentiel_name;  // Assurez-vous que $data->referentiel_name existe
    //     $referentiel_id = $DB->insert_record('mdl_referentiel', $referentiel);
    // } else {
    //     $referentiel_id = $data->referentiel_id;  // Utilisation d'un ID de référentiel existant
    // }

    $record->referentiel = 1;

    // 2. Ajouter une compétence si elle n'existe pas
    // if (isset($data->competency_name) && !record_exists('mdl_competency', 'name', $data->competency_name)) {
    //     $competency = new stdClass();
    //     $competency->name = $data->competency_name;  // Assurez-vous que $data->competency_name existe
    //     $competency->referentiel = $referentiel_id;  // ID du référentiel lié
    //     $competency_id = $DB->insert_record('mdl_competency', $competency);
    // } else {
    //     $competency_id = $data->competency_id;  // Utilisation d'un ID de compétence existant
    // }

    // 3. Ajouter une sous-compétence si elle n'existe pas
    // if (isset($data->subcompetency_name) && !record_exists('mdl_subcompetency', 'name', $data->subcompetency_name)) {
    //     $subcompetency = new stdClass();
    //     $subcompetency->name = $data->subcompetency_name;  // Assurez-vous que $data->subcompetency_name existe
    //     $subcompetency->competency = $competency_id;  // ID de la compétence liée
    //     $subcompetency_id = $DB->insert_record('mdl_subcompetency', $subcompetency);
    // } else {
    //     $subcompetency_id = $data->subcompetency_id;  // Utilisation d'un ID de sous-compétence existant
    // }

    // 4. Ajouter un mot-clé si nécessaire
    // if (isset($data->keyword) && !record_exists('mdl_keyword', 'word', $data->keyword)) {
    //     $keyword_entry = new stdClass();
    //     $keyword_entry->word = $data->keyword;  // Assurez-vous que $data->keyword existe
    //     $keyword_id = $DB->insert_record('mdl_keyword', $keyword_entry);
    // } else {
    //     $keyword_id = $data->keyword_id;  // Utilisation d'un ID de mot-clé existant
    // }

    // 6. Ajouter la relation entre le mot-clé et la sous-compétence
    // if (!record_exists('mdl_subcompetency_keywords', 'keyword_id', $keyword_id, 'subcompetency_id', $subcompetency_id)) {
    //     $subcompetency_keyword = new stdClass();
    //     $subcompetency_keyword->keyword_id = $keyword_id;        // ID du mot-clé
    //     $subcompetency_keyword->subcompetency_id = $subcompetency_id;  // ID de la sous-compétence
    //     $DB->insert_record('mdl_subcompetency_keywords', $subcompetency_keyword);
    // }

    // Vérification du champ 'name'
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
    return $fs->get_file(
        $contextid,
        $component,
        $filearea,
        0,
        '/',
        $filename
    );
}

// function mod_studentqcm_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
//     global $CFG, $DB;

//     require_login();
//     if (!has_capability('mod/studentqcm:view', $context)) {
//         return false;
//     }

//     $fs = get_file_storage();
//     $filename = array_pop($args);
//     $filepath = '/' . implode('/', $args) . '/';
    
//     $file = $fs->get_file($context->id, 'mod_studentqcm', $filearea, 0, $filepath, $filename);
//     if (!$file || $file->is_directory()) {
//         send_file_not_found();
//     }

//     send_stored_file($file, 0, 0, true);
// }


function mod_studentqcm_pluginfile(
    $course,
    $cm,
    $context,
    string $filearea,
    array $args,
    bool $forcedownload
): bool {
    global $DB;

    // Check the contextlevel is as expected - if your plugin is a block, this becomes CONTEXT_BLOCK, etc.
    // if ($context->contextlevel != CONTEXT_MODULE) {
    //     return false;
    // }

    // Make sure the filearea is one of those used by the plugin.
    if ($filearea !== 'questionfiles') {
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
        throw new moodle_exception("Le fichier est introuvable !" . " -- " . $filepath . " -- " . $filearea , 'error', '', $filename);
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





