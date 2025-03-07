<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

// Récupération du module, cours et QCM
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$nbTotalQuestionPop = 0;
$popTypes = $DB->get_records('question_pop', array('refId' => $studentqcm->id));
foreach($popTypes as $popType){
    $nbTotalQuestionPop += $popType->nbqcm + $popType->nbqcu;
}

$nbTotal_question = $studentqcm->nbqcm + $studentqcm->nbqcu + $studentqcm->nbtcs + $nbTotalQuestionPop;

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/admin_grade_gestion.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('student_list', 'mod_studentqcm') . "</p>";
echo "</div>";

// Bouton retour
echo "<div class='flex mt-8 text-lg justify-between'>";
echo "<a href='view.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 text-gray-500 no-underline'>";
echo "<i class='fas fa-arrow-left mr-2'></i>";
echo get_string('back', 'mod_studentqcm');
echo "</a>";
echo "</div>";

// Récupération des étudiants
$students = $DB->get_records('students');

echo '<div class="mt-8">';
echo '<table class="min-w-full bg-white rounded-3xl shadow-md" id="studentTable">';
echo '<thead>';
echo '<tr class="bg-gray-100 text-left">';

// Colonnes du tableau avec arrondis
$columns = [
    'student_id' => get_string('student_id', 'mod_studentqcm'),
    'full_name' => get_string('full_name', 'mod_studentqcm'),
    'total_grade_questions' => get_string('total_grade_questions', 'mod_studentqcm'),
    'total_grade_revisions' => get_string('total_grade_revisions', 'mod_studentqcm'),
    'total_general' => get_string('total_general', 'mod_studentqcm'),
    'last_connected' => get_string('last_connected', 'mod_studentqcm'),
    'actions' => get_string('actions', 'mod_studentqcm')
];

$columnIndex = 0;
foreach ($columns as $key => $label) {
    $roundedClass = ($columnIndex == 0) ? 'rounded-tl-3xl' : (($columnIndex == count($columns) - 1) ? 'rounded-tr-3xl' : '');

    echo '<th class="px-3 py-3 text-sm font-medium text-gray-500 uppercase tracking-wider cursor-pointer ' . $roundedClass . '"
              onclick="sortTable(' . $columnIndex . ')">
              ' . mb_strtoupper($label, 'UTF-8');

    // Ajout du bouton "œil" pour la colonne "Nom complet"
    if ($key === 'full_name') {
        echo ' <button onclick="toggleAllNames()" class="ml-2 px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded-full">';
        echo '<i class="fas fa-eye" id="eye-icon-all"></i></button>';
    }

    echo ' <i class="fas fa-sort ml-2"></i>';
    echo '</th>';
    $columnIndex++;
}

echo '</tr>';
echo '</thead>';
echo '<tbody>';

// Affichage des étudiants
foreach ($students as $student) {
    // Récupérer la somme des notes des questions complétées de l'étudiant
    $sql_questions = "SELECT SUM(grade) as total_grade 
        FROM {studentqcm_question} 
        WHERE userid = ? AND status = 1";
    $total_grade_questions = $DB->get_field_sql($sql_questions, array($student->userid));
    $total_grade_questions = $total_grade_questions !== null ? $total_grade_questions : 0;

    // Récupérer la somme des notes des révisions de l'étudiant
    $sql_revisions = "SELECT SUM(grade) as total_grade 
        FROM {studentqcm_evaluation} 
        WHERE userid = ? AND status = 1";
    $total_grade_revisions = $DB->get_field_sql($sql_revisions, array($student->userid));
    $total_grade_revisions = $total_grade_revisions !== null ? $total_grade_revisions : 0;

    // Calcul du total général
    $total_general = $total_grade_questions + $total_grade_revisions;

    $productions = $DB->get_record('studentqcm_assignedqcm', ['user_id' => $student->userid], 'prod1_id, prod2_id, prod3_id');
    $nbTotal_revision = 0;

    if ($productions) {
        foreach ((array) $productions as $production_id) {
            if (!empty($production_id)) {
                $to_evaluate = $DB->get_records('studentqcm_question', array('userid' => $production_id, 'status' => 1));
                $nbTotal_revision += count($to_evaluate);
            }
        }
    }

    $student_name = $DB->get_record('user', array('id' => $student->userid));
    $student_fullname = ucwords(strtolower($student_name->firstname)) . ' ' . ucwords(strtolower($student_name->lastname));

    echo '<tr class="border-t hover:bg-gray-50">';

    echo '<td class="px-3 py-4 text-md text-gray-600">' . $student->userid . '</td>';

    echo '<td class="px-3 py-4 text-md text-gray-600">';
    echo '<div id="name-' . $student->userid . '" class="text-gray-600 hidden">' . $student_fullname . '</div>';
    echo '</td>';

    echo '<td class="px-3 py-4 text-md text-gray-600">' . $total_grade_questions . " / " . ($nbTotal_question * 5) .  '</td>';
    echo '<td class="px-3 py-4 text-md text-gray-600">' . $total_grade_revisions . " / " . ($nbTotal_revision * 5) .  '</td>';
    echo '<td class="px-3 py-4 text-md text-gray-600">' . $total_general . " / " . ($nbTotal_question * 5 + $nbTotal_revision * 5) .  '</td>';

    echo '<td class="px-3 py-4 text-md text-gray-600">' . 
         ($student_name->lastaccess > 0 
            ? date('d/m/Y', $student_name->lastaccess) 
            : mb_strtoupper(get_string('never_connected', 'mod_studentqcm'), 'UTF-8')) . 
         '</td>';

    echo '<td class="px-3 py-4 text-md text-gray-600 flex space-x-2">';
        echo '<a href="show_production.php?id=' . $id . '&prod_id=' . $student->userid . '" 
                class="px-3 py-2 text-white bg-sky-300 hover:bg-sky-400 rounded-lg shadow-md" 
                title="' . get_string('show_production', 'mod_studentqcm') . '">
                <i class="fas fa-p"></i>
            </a>';
 
        echo '<a href="show_revisions.php?id=' . $id . '&studentid=' . $student->userid . '" 
                class="px-3 py-2 text-white bg-indigo-400 hover:bg-indigo-500 rounded-lg shadow-md" 
                title="' . get_string('show_revision', 'mod_studentqcm') . '">
                <i class="fas fa-r"></i>
            </a>';
    echo '</td>';

    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</div>';

echo $OUTPUT->footer();
?>

<script>
function sortTable(columnIndex) {
    var table = document.getElementById("studentTable");
    var rows = Array.from(table.rows).slice(1);
    var isAscending = table.dataset.sortOrder === "asc";

    rows.sort(function (rowA, rowB) {
        var cellA = rowA.cells[columnIndex].innerText.trim();
        var cellB = rowB.cells[columnIndex].innerText.trim();

        var numA = parseFloat(cellA);
        var numB = parseFloat(cellB);

        if (!isNaN(numA) && !isNaN(numB)) {
            return isAscending ? numA - numB : numB - numA;
        }

        return isAscending ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
    });

    rows.forEach(row => table.appendChild(row));

    table.dataset.sortOrder = isAscending ? "desc" : "asc";
}

function toggleAllNames() {
    var nameDivs = document.querySelectorAll('[id^="name-"]');
    var eyeIcon = document.getElementById('eye-icon-all');

    nameDivs.forEach(nameDiv => nameDiv.classList.toggle('hidden'));
    eyeIcon.classList.toggle('fa-eye-slash');
    eyeIcon.classList.toggle('fa-eye');
}
</script>
