<?php
require_once(__DIR__ . '/../../config.php');

// Vérification si l'utilisateur est connecté et s'il est administrateur
require_login();
if (!is_siteadmin()) {
    echo json_encode(['success' => false, 'message' => 'Accès interdit.']);
    exit;
}

$firstname = required_param('firstname', PARAM_TEXT);
$lastname = required_param('lastname', PARAM_TEXT);
$email = required_param('email', PARAM_EMAIL);
$istiertemps = optional_param('istiertemps', 0, PARAM_INT);

// Debug : afficher les paramètres reçus pour vérifier
// var_dump($_POST); die; // Commenté ou supprimé pour éviter l'interruption

// Vérifier si l'utilisateur existe déjà dans Moodle
$user = $DB->get_record('user', ['email' => $email], 'id');

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'L\'utilisateur n\'existe pas dans Moodle.']);
    exit;
}

// Vérifier si l'utilisateur est déjà inscrit dans mdl_students
$existing_student = $DB->get_record('students', ['userId' => $user->id]);

if ($existing_student) {
    echo json_encode(['success' => false, 'message' => 'L\'étudiant est déjà inscrit.']);
    exit;
}

// Insérer l'étudiant dans la table mdl_students
$new_student = new stdClass();
$new_student->userId = $user->id;
$new_student->isTierTemps = $istiertemps;

try {
    $DB->insert_record('students', $new_student);
    echo json_encode(['success' => true, 'message' => 'L\'étudiant a été ajouté avec succès.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'insertion de l\'étudiant: ' . $e->getMessage()]);
}

exit;
?>
