<?php
/**
 * Lib.php pour le module StudentQCM.
 * Définit les fonctions d'ajout, de mise à jour et de suppression des instances du plugin.
 */

/**
 * Ajouter une nouvelle instance de peerreview dans la base de données.
 *
 * @param stdClass $data Données du formulaire
 * @param mod_peerreview_mod_form $mform Formulaire Moodle (optionnel)
 * @return int ID de l'instance ajoutée
 */
function peerreview_add_instance($data, $mform = null) {
    global $CFG, $DB, $USER;

    require_once("$CFG->libdir/resourcelib.php");
    require_once($CFG->dirroot . '/course/lib.php'); // Importation des fonctions liées aux cours


    // Initialisation des dates
    $data->timecreated = time();
    $data->timemodified = $data->timecreated;

    // Préparer les données du QCM
    $record = new stdClass();

    $session_id = 1;

    //Data informations du référentiel
    $record->name = trim($data->name_plugin);
    $record->intro = isset($data->intro['text']) ? trim($data->intro['text']) : '';
    $record->introformat = isset($data->intro['format']) ? $data->intro['format'] : 0;
    $record->timecreated = $data->timecreated;
    $record->timemodified = $data->timemodified;
    $record->start_date_session = $data->date_start_referentiel;
    $record->end_date_session = $data->date_end_referentiel;
    $record->date_jury = $data->date_jury;
    $record->archived = 0;
   

    // Initialiser les champs de dates
    $date_fields = [
        'start_date_1', 'end_date_1', 'end_date_tt_1',
        'start_date_2', 'end_date_2', 'end_date_tt_2',
        'start_date_3', 'end_date_3', 'end_date_tt_3'
    ];

    $type = 0;
    $index = 0;

    // Validation et récupération des dates
    foreach ($date_fields as $date_field) {
        if (isset($data->$date_field)) {
            $date_value = $data->$date_field;

            // Si la date est une chaîne de texte, la convertir en timestamp
            if (is_string($date_value) && !empty($date_value)) {
                $timestamp = strtotime($date_value); // Convertit la chaîne datetime-local en timestamp
            } else if (empty($date_value)) {
                $timestamp = null; // Autoriser une valeur nulle
            } else {
                $timestamp = $date_value;
            }

            // Vérifier que la conversion a réussi
            if ($timestamp === false) {
                throw new moodle_exception('invaliddate', 'peerreview', '', $date_field);
            }

            // Assigner la date au record si elle est valide
            if ($type != 2 || $data->checkbox_tt_data != "false") {
                $record->$date_field = $timestamp;
            }

            $type++;
            if ($type == 3) {
                $type = 0;
                $index++;
            }
        } else {
            // Si le champ est manquant et que ce n'est pas un champ end_date_tt_X, lever une erreur
            if ($type != 2) { 
                throw new moodle_exception('missingfield', 'peerreview', '', $date_field);
            }
        }
    }



    // Data questions
    $record->nbqcm = $data->choix_qcm;
    $record->nbqcu = $data->choix_qcu;
    $record->nbtcs = $data->choix_tcs;
    $record->nbpop = $data->choix_pop;
    

    $record->nbreviewers = $data->nb_reviewer;

    if (empty($record->name)) {
        throw new moodle_exception('missingfield', 'peerreview', '', 'name');
    }

    // Insérer l'instance principale dans la table 'peerreview'
    $session_id = $DB->insert_record('peerreview', $record);
    if (!$session_id) {
        throw new moodle_exception('insertfailed', 'peerreview');
    }

    $record_referentiel = new stdClass();
    $record_referentiel->name = trim($data->name_referentiel);
    $record_referentiel->sessionid = $session_id;
    $referentiel_id = $DB->insert_record('pr_referentiel', $record_referentiel);

    $record->referentiel = $referentiel_id;
    $record->id = $session_id;
    $DB->update_record('peerreview', $record);

    //Data compétences, sous-compétences, mot-clefs
    if (!empty($data->competences_data) || $data->competences_data == "[]") {
        $competencesArray = json_decode($data->competences_data, true);
        
        foreach ($competencesArray as $competence) {
            // Insérer la compétence
            $comp_record = new stdClass();
            $comp_record->referentiel = $referentiel_id;
            $comp_record->name = trim($competence['name']);
            $comp_record->sessionid = $session_id;
            $competence_id = $DB->insert_record('pr_competency', $comp_record);
    
            // Insérer les sous-compétences
            foreach ($competence['subCompetences'] as $sub) {
                $subcomp_record = new stdClass();
                $subcomp_record->competency = $competence_id;
                $subcomp_record->name = trim($sub['name']);
                $subcomp_record->sessionid = $session_id;
                $subcompetence_id = $DB->insert_record('pr_subcompetency', $subcomp_record);
    
                // Insérer les mots-clés
                foreach ($sub['keywords'] as $keyword) {
                    $key_record = new stdClass();
                    $key_record->word = trim($keyword);
                    $key_record->subcompetency = $subcompetence_id;
                    $key_record->sessionid = $session_id;
                    $DB->insert_record('pr_keyword', $key_record);
                }
            }
        }
    } else {
        throw new moodle_exception('invaliddate', 'peerreview', '', $competences_data);
    }

    $popsArray = json_decode($data->pops_data, true);
    if (!empty($popsArray)) {
        foreach ($popsArray as $pop) {
            // Insérer un pop
            $pop_record = new stdClass();
            $pop_record->nbqcm = $pop['qcm'];
            $pop_record->nbqcu = $pop['qcu'];
            $pop_record->sessionid = $session_id;
            $pop_id = $DB->insert_record('pr_question_pop', $pop_record);
        }
    }
        
    // Course files

    if (!empty($data->courses_files_data)) {
        $selectedCourses = json_decode($data->courses_files_data, true);
        
        foreach($selectedCourses as $fileId => $file){
            $file_record = $DB->get_record('pr_file', ['filearea' => 'coursefiles', 'filename' => $file['file_name']]);
            $competency = $DB->get_record('pr_competency', ['name' => $file['competency_name'], 'sessionid' => $session->id]);
            
            $file_record->id_referentiel = $referentiel_id;
            $file_record->id_competency = $competency->id;
            $file_id = $DB->update_record('pr_file', $file_record);
        }
    }

    if (empty($record->name)) {
        throw new moodle_exception('missingfield', 'peerreview', '', 'name');
    }

    $course_data = new stdClass();
    $course_data->fullname = trim($data->name_plugin);
    $course_data->shortname = trim($data->name_plugin);
    $course_data->category = 1; // ID de la catégorie où placer le cours
    $course_data->visible = 1; // Rendre le cours visible

    $record_course = create_course($course_data); // Création du cours via Moodle

    if (!$record_course) {
        die("Erreur : La création du cours a échoué.");
    } else {
        echo "Cours créé avec succès ! ID : " . $record_course->id . "<br>";
    }

    // $record_studentqcm= $DB->get_record('studentqcm_session', array('id' => $id), '*', MUST_EXIST);
    $record_peerreview = $DB->get_record('peerreview', ['id' => $session_id], '*', MUST_EXIST);
    $record_peerreview->courseid = $record_course->id; 
    // $record_studentqcm->referentiel = $referentiel_id;

    $DB->update_record('peerreview', $record_peerreview);

    return $session_id;
}


/**
 * Mettre à jour une instance existante de peerreview dans la base de données.
 *
 * @param object $data Données mises à jour du formulaire
 * @param object|null $mform Formulaire Moodle (optionnel)
 * @return bool Succès de l'opération
 */
function peerreview_update_instance($data, $mform = null) {
    global $DB;

    // Ajouter la date de modification.
    $data->timemodified = time();

    // L'ID de l'instance est nécessaire pour la mise à jour.
    $data->id = $data->instance;

    // Vérification des champs obligatoires.
    if (!isset($data->name) || empty(trim($data->name))) {
        throw new moodle_exception('missingfield', 'peerreview', '', 'name');
    }

    // Mise à jour des données dans la table peerreview.
    return $DB->update_record('peerreview', $data);
}

/**
 * Supprimer une instance de peerreview de la base de données.
 *
 * @param int $id ID de l'instance à supprimer
 * @return bool Succès de l'opération
 */
function peerreview_delete_instance($id) {
    global $DB;

    // Vérifier si l'instance existe.
    if (!$DB->record_exists('peerreview', array('id' => $id))) {
        throw new moodle_exception('invalidinstance', 'peerreview');
    }

    // Supprimer l'instance de la table peerreview.
    return $DB->delete_records('peerreview', array('id' => $id));
}

/**
 * Retourne une liste de fonctions de rappel pour les hooks du plugin.
 * Utilisé par Moodle dans différents contextes (e.g., backups, resets, etc.).
 *
 * @return array Liste des callbacks disponibles
 */
function peerreview_get_callbacks() {
    return [
        'backup' => 'peerreview_backup_instance',
        'reset'  => 'peerreview_reset_instance',
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

function mod_peerreview_pluginfile(
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
    if (!has_capability('mod/peerreview:view', $context)) {
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

    $file = getImageFile($context->id, 'mod_peerreview', $filearea, $itemid, $filename);
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





