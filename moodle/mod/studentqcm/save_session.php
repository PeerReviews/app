<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

// Vérification de l'authentification
require_login();

$id = required_param('id', PARAM_INT);

// Récupération de l'ID de la session (champ caché dans le formulaire)
$session_id = required_param('session_id', PARAM_INT);

// Récupération de l'enregistrement de la session dans la base de données
$session = $DB->get_record('studentqcm', ['id' => $session_id], '*', MUST_EXIST);

// Vérification du contexte et des permissions
$cm = get_coursemodule_from_instance('studentqcm', $id);
$context = context_module::instance($cm->id);

if (!has_capability('moodle/course:manageactivities', $context)) {
    throw new moodle_exception('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
}

// Mise à jour directe des champs de l'objet session
if (isset($_POST['name'])) {
    $session->name = $_POST['name'];
}

if (isset($_POST['intro'])) {
    $session->intro = $_POST['intro'];
}

if (isset($_POST['referentiel'])) {
    $session->referentiel = $_POST['referentiel'];
}

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

if (isset($_POST['introformat'])) {
    $session->introformat = $_POST['introformat'];
}

if (isset($_POST['start_date_1'])) {
    $session->start_date_1 = strtotime($_POST['start_date_1']);  // Conversion en timestamp
}

if (isset($_POST['end_date_1'])) {
    $session->end_date_1 = strtotime($_POST['end_date_1']);  // Conversion en timestamp
}

if (isset($_POST['end_date_tt_1'])) {
    $session->end_date_tt_1 = strtotime($_POST['end_date_tt_1']);  // Conversion en timestamp
}

if (isset($_POST['start_date_2'])) {
    $session->start_date_2 = strtotime($_POST['start_date_2']);  // Conversion en timestamp
}

if (isset($_POST['end_date_2'])) {
    $session->end_date_2 = strtotime($_POST['end_date_2']);  // Conversion en timestamp
}

if (isset($_POST['end_date_tt_2'])) {
    $session->end_date_tt_2 = strtotime($_POST['end_date_tt_2']);  // Conversion en timestamp
}

if (isset($_POST['start_date_3'])) {
    $session->start_date_3 = strtotime($_POST['start_date_3']);  // Conversion en timestamp
}

if (isset($_POST['end_date_3'])) {
    $session->end_date_3 = strtotime($_POST['end_date_3']);  // Conversion en timestamp
}

if (isset($_POST['end_date_tt_3'])) {
    $session->end_date_tt_3 = strtotime($_POST['end_date_tt_3']);  // Conversion en timestamp
}


// Mise à jour dans la base de données
$session->timemodified = time();

$DB->update_record('studentqcm', $session);


// Redirection avec message de succès
redirect(new moodle_url('/mod/studentqcm/admin_sessions.php', ['id' => $id]), get_string('sessionsaved', 'mod_studentqcm'), null, \core\output\notification::NOTIFY_SUCCESS);
?>
