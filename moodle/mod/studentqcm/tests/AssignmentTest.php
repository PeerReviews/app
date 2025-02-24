<?php
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once(__DIR__ . '/../../../config.php'); // Assure que Moodle est bien chargé
// require_once($CFG->dirroot . '/mod/studentqcm/attribution_qcm.php'); // Remplace par le bon chemin si nécessaire

class AssignmentTest extends advanced_testcase {

    public function test_assignments() {
        global $DB;

        // Réinitialiser l'environnement de test
        $this->resetAfterTest(true);

        // Exécuter l'attribution des productions
        // require($CFG->dirroot . '/mod/studentqcm/attribution_qcm.php');

        // Récupérer les attributions en base
        $assignments = $DB->get_records('pr_studentqcm_assignedqcm');

        // Vérifier les règles d'assignation
        $production_count = [];
        foreach ($assignments as $assignment) {
            $user_id = $assignment->user_id;
            $prod1_id = $assignment->prod1_id;
            $prod2_id = $assignment->prod2_id;
            $prod3_id = $assignment->prod3_id ?? null;

            // Chaque étudiant doit avoir au moins deux productions
            $this->assertNotNull($prod1_id, "L'étudiant $user_id n'a pas de prod1_id assigné.");
            $this->assertNotNull($prod2_id, "L'étudiant $user_id n'a pas de prod2_id assigné.");

            // prod1_id et prod2_id doivent être différents
            $this->assertNotEquals($prod1_id, $prod2_id, "prod1_id et prod2_id sont identiques pour l'étudiant $user_id.");
            $this->assertNotEquals($user_id, $prod1_id, "L'étudiant $user_id ne peut pas s'assigner lui-même en prod1_id.");
            $this->assertNotEquals($user_id, $prod2_id, "L'étudiant $user_id ne peut pas s'assigner lui-même en prod2_id.");

            // Si prod3_id existe, il doit être différent des autres
            if ($prod3_id !== null) {
                $this->assertNotEquals($prod1_id, $prod3_id, "prod1_id et prod3_id sont identiques pour l'étudiant $user_id.");
                $this->assertNotEquals($prod2_id, $prod3_id, "prod2_id et prod3_id sont identiques pour l'étudiant $user_id.");
                $this->assertNotEquals($user_id, $prod3_id, "L'étudiant $user_id ne peut pas s'assigner lui-même en prod3_id.");
            }

            // Compter combien de fois chaque production est assignée
            $production_count[$prod1_id] = ($production_count[$prod1_id] ?? 0) + 1;
            $production_count[$prod2_id] = ($production_count[$prod2_id] ?? 0) + 1;
            if ($prod3_id !== null) {
                $production_count[$prod3_id] = ($production_count[$prod3_id] ?? 0) + 1;
            }
        }

        // Vérifier que chaque production est assignée à au moins deux étudiants
        foreach ($production_count as $prod_id => $count) {
            $this->assertGreaterThanOrEqual(2, $count, "La production $prod_id n'est pas assignée à au moins deux étudiants.");
            print "✅ La production $prod_id est bien assignée à au moins deux étudiants.\n";
        }

        print "✅✅✅ Test terminé avec succès !\n";

    }
}
