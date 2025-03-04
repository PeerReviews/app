<?php
define('CLI_SCRIPT', true); // Indique à Moodle que ce script est exécuté en CLI
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/user/lib.php');

global $DB, $USER;

$nb_users = 100; // Nombre d'utilisateurs à créer
$context = context_system::instance(); // Contexte global du site

for ($i = 1; $i <= $nb_users; $i++) {
    $newuser = new stdClass();
    $newuser->username  = "user$i";
    $newuser->password  = 'MotDePasse123!'; // Moodle hashera automatiquement
    $newuser->firstname = "User$i";
    $newuser->lastname  = "Test";
    $newuser->email     = "user$i@example.com";
    $newuser->auth      = 'manual';
    $newuser->confirmed = 1;
    $newuser->mnethostid = 1;

    // Vérifier si l'utilisateur existe déjà
    if ($DB->record_exists('user', ['username' => $newuser->username])) {
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
        $student->userId = $newuser->id;
        $student->isTierTemps = 0;
        

        $DB->insert_record('students', $student);
        echo "Ajouté à la table mdl_students.\n";
    } else {
        echo "Erreur lors de la création de l'utilisateur {$newuser->username}.\n";
    }
}

echo "Processus terminé !";
?>
