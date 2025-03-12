<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

// Récupération du module, cours et QCM
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$session = $DB->get_record('studentqcm', ['archived' => 0], '*', MUST_EXIST);

$nbTotalQuestionPop = 0;
$popTypes = $DB->get_records('question_pop', array('sessionid' => $studentqcm->id));
foreach($popTypes as $popType){
    $nbTotalQuestionPop += $popType->nbqcm + $popType->nbqcu;
}

$nbTotal_question = $studentqcm->nbqcm + $studentqcm->nbqcu + $studentqcm->nbtcs + $nbTotalQuestionPop;

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/student_dashboard_phase1.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

// Vérifie si le professeur a cliqué sur un bouton pour changer d'utilisateur
if (isset($_GET['switch_to_user'])) {
    $student_id = required_param('switch_to_user', PARAM_INT);
    $student = $DB->get_record('user', array('id' => $student_id), '*', MUST_EXIST);
    $USER->id = $student->id;

    complete_user_login($student);

    redirect(new moodle_url('/mod/studentqcm/view.php', array('id' => $id)));
}

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
$students = $DB->get_records('students', ['sessionid' => $session->id]);

echo '<div class="mt-8">';
echo '<table class="min-w-full bg-white rounded-3xl shadow-md" id="studentTable">';
echo '<thead>';
echo '<tr class="bg-gray-100 text-left">';

// Colonnes du tableau avec arrondis
$columns = [
    'student_id' => get_string('student_id', 'mod_studentqcm'),
    'full_name' => get_string('full_name', 'mod_studentqcm'),
    'completed_question' => get_string('completed_question', 'mod_studentqcm'),
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
    $completed_questions_count = $DB->count_records('studentqcm_question', array('userid' => $student->userid, 'sessionid' => $session->id, 'status' => 1));

    $student_name = $DB->get_record('user', array('id' => $student->userid));
    $student_fullname = ucwords(strtolower($student_name->firstname)) . ' ' . ucwords(strtolower($student_name->lastname));

    echo '<tr class="border-t hover:bg-gray-50">';

    echo '<td class="px-3 py-4 text-md text-gray-600">' . $student->userid . '</td>';

    echo '<td class="px-3 py-4 text-md text-gray-600">';
    echo '<div id="name-' . $student->userid . '" class="text-gray-600 hidden">' . $student_fullname . '</div>';
    echo '</td>';

    $colorClass = ($completed_questions_count == 0) ? 'text-red-400' : 
                  (($completed_questions_count == $nbTotal_question) ? 'text-lime-500' : 'text-gray-600');

    echo '<td class="px-3 py-4 text-md ' . $colorClass . '">' . $completed_questions_count . " / " . $nbTotal_question .  '</td>';

    echo '<td class="px-3 py-4 text-md text-gray-600">' . 
         ($student_name->lastaccess > 0 
            ? date('d/m/Y', $student_name->lastaccess) 
            : mb_strtoupper(get_string('never_connected', 'mod_studentqcm'), 'UTF-8')) . 
         '</td>';


    echo '<td class="px-2 py-4 text-md text-gray-600">';
    echo '<a href="' . new moodle_url($PAGE->url, array('switch_to_user' => $student->userid)) . '" class="px-4 py-2 min-w-40 bg-indigo-400 hover:bg-indigo-500 text-white text-md font-semibold rounded-2xl">';
    echo '<i class="fas fa-people-pulling mr-2"></i>' . mb_strtoupper(get_string('connect', 'mod_studentqcm'), 'UTF-8');
    echo "</a>";
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
