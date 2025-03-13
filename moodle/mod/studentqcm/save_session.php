<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Vérification de l'authentification
require_login();

$id = required_param('id', PARAM_INT);

// Récupération de l'ID de la session (champ caché dans le formulaire)
$session_id = required_param('session_id', PARAM_INT);

print_r($id);
print_r($session_id);

// Récupération de l'enregistrement de la session dans la base de données
$session = $DB->get_record('studentqcm', ['id' => $session_id], '*', MUST_EXIST);
print_r($session);

// Vérification du contexte et des permissions
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$context = context_module::instance($cm->id);

if (!has_capability('moodle/course:manageactivities', $context)) {
    throw new moodle_exception('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
}
// Liste des champs à exclure
$exclude_fields = ['nbqcm', 'nbqcu', 'nbpop', 'nbtcs'];

// Parcours des champs de $_POST
foreach ($_POST as $key => $value) {
    if (in_array($key, $exclude_fields)) {
        continue;
    }

    // Gestion spécifique pour le champ referentiel
    if ($key === 'referentiel') {
        $referentiel_record = $DB->get_record_sql(
            'SELECT * FROM {referentiel} WHERE ' . $DB->sql_compare_text('name') . ' = :name',
            ['name' => $value, 'sessionid' => $session->id]
        );

        if (!$referentiel_record) {
            $referentiel_id = $DB->insert_record('referentiel', ['name' => $value, 'sessionid' => $session->id]);
        } 
        else {
            $referentiel_id = $referentiel_record->id;
        }

        $session->referentiel = intval($referentiel_id);
      
        continue; // Passe au champ suivant
    }

    // Gestion des dates (conversion en timestamp)
    if (strpos($key, 'date') !== false){
        $session->$key = strtotime($value);
    } 
    else {
        $session->$key = $value;
    }
}

// Mise à jour des champs exclus manuellement
if (isset($_POST['nbqcm'])) {
    $session->nbQcm = $_POST['nbqcm'];
}

if (isset($_POST['nbqcu'])) {
    $session->nbQcu = $_POST['nbqcu'];
}

if (isset($_POST['nbtcs'])) {
    $session->nbTcs = $_POST['nbtcs'];
}

if (isset($_POST['nbpop'])) {
    $session->nbPop = $_POST['nbpop'];
}

// Mise à jour dans la base de données
$session->timemodified = time();
$DB->update_record('studentqcm', $session);

// Redirection avec message de succès
// redirect(new moodle_url('/mod/studentqcm/admin_sessions.php', ['id' => $id]), get_string('sessionsaved', 'mod_studentqcm'), null, \core\output\notification::NOTIFY_SUCCESS);
?>