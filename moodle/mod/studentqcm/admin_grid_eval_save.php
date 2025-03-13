<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

$gridData = required_param('gridData', PARAM_RAW);
$gridData = json_decode($gridData, true);

$session = $DB->get_record('studentqcm', ['archived' => 0], '*', MUST_EXIST);
require_login();

$id_grille_qcu = (int) $session->grid_eval_qcu;
$id_grille_qcm = (int) $session->grid_eval_qcm;
$id_grille_tcs = (int) $session->grid_eval_tcs;

$record_qcu = new stdClass();
$record_qcu->id = $id_grille_qcu;

$record_qcm = new stdClass();
$record_qcm->id = $id_grille_qcm;

$record_tcs = new stdClass();
$record_tcs->id = $id_grille_tcs;

$i = 1;
$type = 0;
foreach ($gridData["qcu"] as $grid) {
    if ($type == 0) {
        foreach ($grid as $bonus) {
            $record_qcu->{"bonus" . $i} = $bonus;
            $i++;
        }
        $i = 1;
        $type++;
    } else {
        foreach ($grid as $malus) {
            $record_qcu->{"malus" . $i} = $malus;
            $i++;
        }
    }
}

$i = 1;
$type = 0;
foreach ($gridData["qcm"] as $grid) {
    if ($type == 0) {
        foreach ($grid as $bonus) {
            $record_qcm->{"bonus" . $i} = $bonus;
            $i++;
        }
        $i = 1;
        $type++;
    } else {
        foreach ($grid as $malus) {
            $record_qcm->{"malus" . $i} = $malus;
            $i++;
        }
    }
}

$i = 1;
$type = 0;
foreach ($gridData["tcs"] as $grid) {
    if ($type == 0) {
        foreach ($grid as $bonus) {
            $record_tcs->{"bonus" . $i} = $bonus;
            $i++;
        }
        $i = 1;
        $type++;
    } else {
        foreach ($grid as $malus) {
            $record_tcs->{"malus" . $i} = $malus;
            $i++;
        }
    }
}

// echo "<pre>";
// print_r($gridData);
// print_r($record_qcm);
// print_r($record_tcs);
// echo "</pre>";
// exit;


$DB->update_record('grid_eval', $record_qcu);
$DB->update_record('grid_eval', $record_qcm);
$DB->update_record('grid_eval', $record_tcs);

redirect(new moodle_url('/mod/studentqcm/admin_grid_eval.php', ['id' => $id]));
?>