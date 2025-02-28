<?php
define('CLI_SCRIPT', true); // Indique à Moodle que ce script est exécuté en CLI
require_once(__DIR__ . '/../../config.php');

global $DB;

$students = $DB->get_records('user', null, '', 'id');
$student_ids = array_keys($students);

if (count($student_ids) < 2) {
    die("Il faut au moins 2 étudiants pour faire l'attribution !");
}

shuffle($student_ids); // Mélanger la liste des étudiants

// Dictionnaire pour compter les assignations
$assignment_count = array_fill_keys($student_ids, 0);

// Liste des attributions
$assignments = [];

foreach ($student_ids as $student_id) {
    $assignments[$student_id] = [];

    // Récupérer la liste des étudiants disponibles (pas soi-même et pas déjà 3 assignations)
    $possible_assignees = array_values(array_filter($student_ids, function ($id) use ($student_id, $assignment_count) {
        return $id !== $student_id && ($assignment_count[$id] ?? 0) < 3;
    }));

    shuffle($possible_assignees);

    // Déterminer combien on doit en assigner (2 ou 3)
    $num_assignees = min(rand(2, 3), count($possible_assignees));
    $assigned_students = array_slice($possible_assignees, 0, $num_assignees);

    // Stocker les attributions
    $record = new stdClass();
    $record->user_id = $student_id;
    $record->prod1_id = $assigned_students[0] ?? null;
    $record->prod2_id = $assigned_students[1] ?? null;
    $record->prod3_id = $assigned_students[2] ?? null;

    $DB->insert_record('studentqcm_assignedqcm', $record);

    // Mettre à jour le compteur d'assignations
    foreach ($assigned_students as $assignee) {
        $assignment_count[$assignee] = ($assignment_count[$assignee] ?? 0) + 1;
        
        // Si un étudiant atteint 3 assignations, on le retire des choix futurs
        if ($assignment_count[$assignee] >= 3) {
            unset($assignment_count[$assignee]);
        }
    }
}

echo "Les étudiants ont été assignés en respectant la limite de 3 assignations maximum !\n";

?>
