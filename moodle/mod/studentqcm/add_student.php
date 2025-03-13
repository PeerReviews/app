<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

// Vérification si l'utilisateur est connecté et s'il est administrateur
require_login();
if (!is_siteadmin()) {
    echo json_encode(['success' => false, 'message' => 'Accès interdit.']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_data = [];
    foreach ($_POST as $key => $value) {
        if ($key !== "add_student") { // Exclure le champ du bouton
            $student_data[$key] = trim($value);
        }
    }

}
// Vérifier si l'utilisateur existe déjà dans Moodle
$user = $DB->get_record('user', ['email' => $student_data['email']], 'id');

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'L\'utilisateur n\'existe pas dans Moodle.']);
    exit;
}

$session = $DB->get_record('studentqcm_session', ['archived' => 0], '*', MUST_EXIST);

// Vérifier si l'utilisateur est déjà inscrit dans mdl_students
$existing_student = $DB->get_record('students', ['userId' => $user->id, 'sessionid' => $session->id]);

if ($existing_student) {
    echo json_encode(['success' => false, 'message' => 'L\'étudiant est déjà inscrit.']);
    exit;
}

// Insérer l'étudiant dans la table mdl_students
$new_student = new stdClass();
$new_student->userId = $user->id;
$new_student->isTierTemps = $student_data['istiertemps'];
$new_student->sessionid = $session->id;

try {
    $DB->insert_record('students', $new_student);
    echo json_encode(['success' => true, 'message' => 'L\'étudiant a été ajouté avec succès.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'insertion de l\'étudiant: ' . $e->getMessage()]);
}

redirect(new moodle_url('/mod/studentqcm/view.php', array('id' => $id)));
?>
