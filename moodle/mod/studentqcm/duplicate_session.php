<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);
$session = $DB->get_record('studentqcm', ['id' => $session_id], '*', MUST_EXIST);

require_login();

// Déterminer un nom unique pour la nouvelle session
$base_name = $session->name;
$pattern = '/^' . preg_quote($base_name, '/') . ' \((\d+)\)$/';

// Récupérer les sessions existantes avec des noms similaires
$existing_names = $DB->get_records_sql("SELECT name FROM {studentqcm} WHERE name LIKE ?", ["$base_name%"]);

$max_number = 1;
foreach ($existing_names as $record) {
    if (preg_match($pattern, $record->name, $matches)) {
        $number = (int) $matches[1];
        if ($number > $max_number) {
            $max_number = $number;
        }
    }
}

// Déterminer le nouveau nom
$new_name = $max_number > 1 ? "$base_name (" . ($max_number + 1) . ")" : "$base_name (2)";

// Créer une nouvelle session avec les mêmes données
$new_session = clone $session;
unset($new_session->id); // Ne pas dupliquer l'ID
$new_session->name = $new_name;
$new_session->timecreated = time(); // Mettre à jour la date de création

$new_session_id = $DB->insert_record('studentqcm', $new_session);

redirect(new moodle_url('/mod/studentqcm/admin_sessions.php', ['id' => $id]));


?>
