<?php

// Inclure le fichier de configuration de Moodle pour initialiser l'environnement Moodle
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours depuis l'URL
$id = required_param('id', PARAM_INT);

// Obtenir les informations du module de cours
$cm = get_coursemodule_from_id('peerreview', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$peerreview = $DB->get_record('peerreview', array('id' => $cm->instance), '*', MUST_EXIST);

$session = $DB->get_record('peerreview', ['archived' => 0], '*', MUST_EXIST);

// Vérifier que l'utilisateur est connecté
require_login($course, true, $cm);
$user_id = $USER->id;

// Récupérer les productions assignées à l'étudiant
$assigned_students = $DB->get_records('pr_assigned_student_teacher', array('teacherid' => $user_id, 'sessionid' => $session->id), 'userid');


// Définir l'URL de la page et les informations de la page
$PAGE->set_url('/mod/peerreview/teacher_production_list.php', array('id' => $id));
$PAGE->set_title(format_string($peerreview->name));
$PAGE->set_heading(format_string($course->fullname));

// Charger les fichiers CSS nécessaires
$PAGE->requires->css(new moodle_url('/mod/peerreview/style.css', array('v' => time())));

// Afficher l'en-tête de la page
echo $OUTPUT->header();

echo "<div class='mx-auto'>";
    echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('assigned_prod_list', 'mod_peerreview') . "</p>";
echo "</div>";

// Bouton de retour
echo "<div class='flex mt-8 text-lg justify-start'>";
    echo "<a href='view.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
    echo "<i class='fas fa-arrow-left mr-2'></i>";
    echo get_string('back', 'mod_peerreview');
    echo "</a>";
echo "</div>";

if ($assigned_students) {
    echo "<div class='space-y-4 mt-4'>";
    $nb = 1;

    foreach ($assigned_students as $student) {
        
        $prod_id = $student->userid;

        // Charger les questions de la production assignée
        $qcms = array();

        // Vérifier si l'ID de la production assignée est valide
        if (!empty($prod_id)) {
            $questions = $DB->get_records('pr_question', array('userid' => $prod_id, 'sessionid' => $session->id));

            foreach ($questions as $question) {
                $qcms[] = $question;
            }
        }

        $nb_eval_questions = count(array_filter($qcms, function($q) {
            return $q->grade !== null;
        }));

        $nb_evaluated_revisions = 0;
        $nb_total_revisions = 0;

        foreach ($qcms as $qcm) {
            $evaluations = $DB->get_records('pr_evaluation', array('question_id' => $qcm->id));
            $nb_total_revisions += count($evaluations);

            foreach ($evaluations as $evaluation) {
                if ($evaluation->grade != null ) {
                    $nb_evaluated_revisions++;
                }
            }
        }


        echo "<div class='p-4 bg-white rounded-3xl shadow flex items-center justify-between'>";
            echo "<p class='font-semibold text-2xl text-gray-700 flex items-center gap-2'>";
            echo "<i class='fas fa-clipboard text-indigo-400 mr-2'></i>";
            echo "Production " . ($nb++);
            echo "</p>";

            // Déterminer si toutes les évaluations sont terminées
            $all_evaluated = ($nb_eval_questions == count($qcms)) && ($nb_evaluated_revisions == $nb_total_revisions);

            // Définition des classes CSS et de l'icône
            $button_class = $all_evaluated ? "bg-lime-400 hover:bg-lime-500" : "bg-indigo-400 hover:bg-indigo-500";
            $icon_class = $all_evaluated ? "fas fa-check-circle" : "fas fa-pen-to-square";
            $button_text = $all_evaluated ? get_string('evaluated', 'mod_peerreview') : get_string('evaluate', 'mod_peerreview');

            // Affichage du bouton
            echo "<div class='flex items-center text-xl text-gray-700'>";
                echo "<p class='mr-8 font-medium text-lg'>" . get_string('nb_evaluated_question', 'mod_peerreview') . " : <span id='nb-eval-questions'>" . $nb_eval_questions . " / " . count($qcms) . "</span></p>";
                echo "<p class='mr-8 font-medium text-lg'>" . get_string('nb_evaluated_revision', 'mod_peerreview') . " : <span id='nb-eval-revisions'>" . $nb_evaluated_revisions . " / " . $nb_total_revisions . "</span></p>";

                echo "<a href='teacher_production_eval.php?id={$id}&prod_id={$prod_id}' class='px-4 py-2 text-white text-lg font-semibold rounded-2xl $button_class'>";
                echo "<i class='$icon_class mr-2'></i> $button_text";
                echo "</a>";
            echo "</div>";

        echo "</div>";
        
    }
    
    echo "</div>";
} else {
    echo "<p class='text-center text-lg text-gray-600'>" . get_string('no_assigned_prod', 'mod_peerreview') . "</p>";
}

echo $OUTPUT->footer();
