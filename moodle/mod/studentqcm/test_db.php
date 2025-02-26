<?php
$mysqli = new mysqli(getenv('MOODLE_DATABASE_HOST'), getenv('MOODLE_DATABASE_USER'), getenv('MOODLE_DATABASE_PASSWORD'), getenv('MOODLE_DATABASE_NAME'));

if ($mysqli->connect_error) {
    die("Échec de la connexion : " . $mysqli->connect_error);
} else {
    echo "Connexion réussie à la base de données !";
}
?>
