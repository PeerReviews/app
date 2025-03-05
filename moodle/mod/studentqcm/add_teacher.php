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
    $teacher_data = [];
    foreach ($_POST as $key => $value) {
        if ($key !== "add_teacher") {
            $teacher_data[$key] = trim($value);
        }
    }

}
// Vérifier si l'utilisateur existe déjà dans Moodle
$user = $DB->get_record('user', ['email' => $teacher_data['email']], 'id');

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'L\'utilisateur n\'existe pas dans Moodle.']);
    exit;
}

// Vérifier si l'utilisateur est déjà inscrit dans mdl_teachers
$existing_teacher = $DB->get_record('teachers', ['userId' => $user->id]);

if ($existing_teacher) {
    echo json_encode(['success' => false, 'message' => 'L\'enseignant est déjà inscrit.']);
    exit;
}

// Insérer l'enseignant dans la table mdl_teachers
$new_teacher = new stdClass();
$new_teacher->userId = $user->id;

try {
    $DB->insert_record('teachers', $new_teacher);
    echo json_encode(['success' => true, 'message' => 'L\'enseignant a été ajouté avec succès.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'insertion de l\'enseignant: ' . $e->getMessage()]);
}

redirect(new moodle_url('/mod/studentqcm/admin_add_user.php', array('id' => $id)));
?>
