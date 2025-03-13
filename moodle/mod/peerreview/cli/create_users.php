<?php
define('CLI_SCRIPT', true); // Indique à Moodle que ce script est exécuté en CLI
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/user/lib.php');

global $DB, $USER;

$session = $DB->get_record('peerreview', ['archived' => 0], '*', MUST_EXIST);

$nb_users = 100; // Nombre d'utilisateurs à créer
$context = context_system::instance(); // Contexte global du site

$roleid = 5;

$capability = 'moodle/course:managefiles';
$permission = CAP_ALLOW;  // Permettre la capacité

// Vérifier si la capacité existe pour ce rôle
$existing_capability = $DB->get_record('role_capabilities', array('roleid' => $roleid, 'capability' => $capability));

// Si la capacité n'existe pas, on l'ajoute
if (!$existing_capability) {
    $rolecap = new stdClass();
    $rolecap->roleid = $roleid;
    $rolecap->capability = $capability;
    $rolecap->permission = $permission;

    // Insérer la capacité dans la base de données
    $DB->insert_record('role_capabilities', $rolecap);
} else {
    // Sinon, mettre à jour la capacité
    $rolecap = new stdClass();
    $rolecap->id = $existing_capability->id; // On obtient l'ID de l'enregistrement existant
    $rolecap->roleid = $roleid;
    $rolecap->capability = $capability;
    $rolecap->permission = $permission;

    // Mettre à jour la capacité dans la base de données
    $DB->update_record('role_capabilities', $rolecap);
}

for ($i = 1; $i <= $nb_users; $i++) {
    $newuser = new stdClass();
    $newuser->id = 6;
    $newuser->username  = "testimage";
    $newuser->password  = 'MotDePasse123!'; // Moodle hashera automatiquement
    $newuser->firstname = "User$i";
    $newuser->lastname  = "Test";
    $newuser->email     = "user$i@example.com";
    $newuser->auth      = 'manual';
    $newuser->confirmed = 1;
    $newuser->mnethostid = 1;

    // Vérifier si l'utilisateur existe déjà
    if ($userid = $DB->record_exists('user', ['username' => $newuser->username])) {
        echo "Utilisateur {$newuser->username} existe déjà, passage au suivant.\n";
        continue;
    }

    // Création de l'utilisateur
    $newuser->id = user_create_user($newuser);

    if ($newuser->id) {
        echo "Utilisateur {$newuser->username} créé avec succès, ID: {$newuser->id}\n";

        role_assign($roleid, $newuser->id, $context->id); // Attribution du rôle
        echo "Rôle 'Student' assigné à l'utilisateur {$newuser->username}.\n";

        // Insertion dans la table mdl_students
        $student = new stdClass();
        $student->userid = $newuser->id;
        $student->istiertemps = 0;
        $student->sessionid = $session->id;
        $roleid = 5;

        // Assignation du rôle à l'utilisateur
        role_assign($roleid, $newuser->id, $context->id);

        $DB->insert_record('pr_students', $student);
        echo "Ajouté à la table mdl_pr_students.\n";

    } else {
        echo "Erreur lors de la création de l'utilisateur {$newuser->username}.\n";
    }
}

echo "Processus terminé !";
?>
