<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);
$session = $DB->get_record('peerreview', ['id' => $session_id], '*', MUST_EXIST);

require_login();

// Déterminer un nom unique pour la nouvelle session
$base_name = $session->name;
$pattern = '/^' . preg_quote($base_name, '/') . ' \((\d+)\)$/';

// Récupérer les sessions existantes avec des noms similaires
$existing_names = $DB->get_records_sql("SELECT name FROM {peerreview} WHERE name LIKE ?", ["$base_name%"]);

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
$new_session->archived = 1;
$new_session->timecreated = time(); // Mettre à jour la date de création

$new_session_id = $DB->insert_record('peerreview', $new_session);

// Duplication de l'arbre de référentiel
$referentiel_id = $session->referentiel;
$referentiel = $DB->get_record('pr_referentiel', ['id' => $referentiel_id, 'sessionid' => $session_id], '*', MUST_EXIST);
$new_referentiel = clone $referentiel;
$new_referentiel->sessionid = $new_session_id;
$session->referentiel = $new_referentiel->id;

// Récupérer les compétences associées au référentiel de base
$competencies = $DB->get_records('pr_competency', ['referentiel' => $referentiel_id, 'sessionid' => $session->id]);
foreach($competencies as $competency){
    $new_competency = clone $competency;
    $new_competency->sessionid = $new_session_id;
    $new_competency->referentiel = $new_referentiel->id;
    $new_competency_id = $DB->insert_record('pr_competency', $new_competency);

    // Récupérer les sous-compétences associées à la compétence de base
    $subcompetencies = $DB->get_records('pr_subcompetency', ['competency' => $competency->id, 'sessionid' => $session->id]);
    foreach($subcompetencies as $subcompetency){
        $new_subcompetency = clone $subcompetency;
        $new_subcompetency->sessionid = $new_session_id;
        $new_subcompetency->competency = $new_competency_id;
        $new_subcompetency_id = $DB->insert_record('pr_subcompetency', $new_subcompetency);

        $keywords = $DB->get_records('pr_keyword', ['subcompetency' => $subcompetency->id, 'sessionid' => $session->id]);
        foreach($keywords as $keyword){
            $new_keyword = clone $keyword;
            $new_keyword->sessionid = $new_session_id;
            $new_keyword->subcompetency = $new_subcompetency_id;
            $DB->insert_record('pr_keyword', $new_keyword);
        }
    }
}

redirect(new moodle_url('/mod/peerreview/admin_sessions.php', ['id' => $id]));


?>
