<?php
require_once(__DIR__ . '/../../config.php');

// Vérifier si l'ID du référentiel a été envoyé
$referentiel_id = required_param('referentiel_id', PARAM_INT);

// Vérifier si des compétences existent pour ce référentiel
$competencies = $DB->get_records('competency', array('referentiel' => $referentiel_id));

if ($competencies) {
    foreach ($competencies as $competency) {
        echo "<option value='{$competency->id}'>{$competency->name}</option>";
    }
} else {
    echo "<option value=''>Aucune compétence disponible</option>";
}
?>
