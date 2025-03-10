<?php
define('AJAX_SCRIPT', true);
require_once('../../config.php');
require_login();
require_sesskey();

use mod_studentqcm\task\attribution_student_task;

ob_start(); // Capture la sortie de mtrace()

$task = new attribution_student_task();
$task->execute($force = true);

$output = ob_get_clean(); // Récupérer tout le contenu généré par mtrace()
$response = [
    'success' => true,
    'message' => nl2br(trim($output)) // Convertir les retours à la ligne pour affichage HTML
];

echo json_encode($response);
exit;
