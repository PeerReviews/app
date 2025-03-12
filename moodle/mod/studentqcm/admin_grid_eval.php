<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

// Récupération du module, cours 
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/admin_grid_eval.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('grid_eval_title', 'mod_studentqcm') . "</p>";
echo "</div>";

// Bouton retour
echo "<div class='flex mt-8 text-lg justify-between'>";
echo "<a href='view.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 text-gray-500 no-underline'>";
echo "<i class='fas fa-arrow-left mr-2'></i>";
echo get_string('back', 'mod_studentqcm');
echo "</a>";
echo "</div>";

$studentqcm = $DB->get_record('studentqcm', ['archived' => 0], '*', IGNORE_MULTIPLE);

$grid_eval_qcu = null;
$grid_eval_qcm = null;
$grid_eval_tcs = null;

if ($studentqcm) {
    // Récupérer les grilles associées aux QCU, QCM et TCS
    $grid_eval_qcu = $DB->get_record('grid_eval', ['id' => $studentqcm->grid_eval_qcu]);
    $grid_eval_qcm = $DB->get_record('grid_eval', ['id' => $studentqcm->grid_eval_qcm]);
    $grid_eval_tcs = $DB->get_record('grid_eval', ['id' => $studentqcm->grid_eval_tcs]);

    echo "<div id='grid-qcu' class=''>";
    echo "<p class='font-bold text-2xl ml-4 m-8'>" . get_string('grid_eval_qcu', 'mod_studentqcm') . "</p>";
    echo "<div class=''>";
    echo "<div id='affichage-grid-qcu'>";
    if ($grid_eval_qcu) {
        $index = 0;
        foreach($grid_eval_qcu as $element ) {
            if ($index<6 && $index!=0){
                echo "<p>+1: ".$element."</p>";
            }
            if ($index>=6 && $index!=0) {
                if ($element) {
                echo "<p>-1: ".$element."</p>";
                }
            }
            $index++;
        };
    }
    echo "</div>";
    echo "</div>";
    echo "</div>";

    echo "<div id='grid-qcm' class=''>";
    echo "<p class='font-bold text-2xl ml-4 m-8'>" . get_string('grid_eval_qcm', 'mod_studentqcm') . "</p>";
    echo "<div class=''>";
    echo "<div id='affichage-grid-qcm'>";
    if ($grid_eval_qcm) {
        $index = 0;
        foreach($grid_eval_qcm as $element ) {
            if ($index<6 && $index!=0){
                echo "<label>+1: <input type='text' name='bonus$index' value='".htmlspecialchars($element, ENT_QUOTES)."'></label><br>";
            }
            if ($index>=6 && $index!=0) {
                if ($element) {
                    echo "<label>-1: <input type='text' name='malus$index' value='".htmlspecialchars($element, ENT_QUOTES)."'></label><br>";
                }
            }
            $index++;
        };
    }
    echo "</div>";
    echo "</div>";
    echo "</div>";

    echo "<div id='grid-tcs' class=''>";
    echo "<p class='font-bold text-2xl ml-4 m-8'>" . get_string('grid_eval_tcs', 'mod_studentqcm') . "</p>";
    echo "<div class=''>";
    echo "<div id='affichage-grid-tcs'>";
    if ($grid_eval_tcs) {
        $index = 0;
        foreach($grid_eval_tcs as $element ) {
            if ($index<6 && $index!=0){
                echo "<p>+1: ".$element."</p>";
            } 
            if ($index>=6 && $index!=0) {
                if ($element) {
                echo "<p>-1: ".$element."</p>";
                }
            }
            $index++;
        };
    }
    echo "</div>";
    echo "</div>";
    echo "</div>";

}

echo "<button id='export-grid'>" . get_string('export', 'mod_studentqcm') . "</button>";
echo "<button id='import-grid'>" . get_string('export', 'mod_studentqcm') . "</button>";


echo $OUTPUT->footer();
?>

<script>
    let grid_qcu = <?php echo json_encode($grid_eval_qcu); ?>;

    function affichageGridQCU() {
        
    }
</script>