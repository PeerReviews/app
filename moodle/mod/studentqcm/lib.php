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

    // Nettoyer les champs pour correspondre à la table
    $record = new stdClass();
    $record->name = trim($data->name);
    $record->intro = isset($data->intro['text']) ? trim($data->intro['text']) : '';
    $record->timecreated = $data->timecreated;
    $record->timemodified = $data->timemodified;

    // Vérification des champs obligatoires
    if (empty($record->name)) {
        throw new moodle_exception('missingfield', 'studentqcm', '', 'name');
    }
    if (empty($record->intro)) {
        throw new moodle_exception('missingfield', 'studentqcm', '', 'intro');
    }

    // Log pour vérification des données avant insertion
    error_log(print_r($record, true));

    // Insérer dans la table studentqcm
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



