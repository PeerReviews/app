<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

// Récupération du module, cours et QCM
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/teacher_dashboard.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css'));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('teacher_list', 'mod_studentqcm') . "</p>";
echo "</div>";

// Bouton retour
echo "<div class='flex mt-8 text-lg justify-between'>";
echo "<a href='view.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 text-gray-500 no-underline'>";
echo "<i class='fas fa-arrow-left mr-2'></i>";
echo get_string('back', 'mod_studentqcm');
echo "</a>";
echo "</div>";

// Récupération des enseignants
$teachers = $DB->get_records('teachers');

echo '<div class="mt-8">';
echo '<table class="min-w-full bg-white rounded-3xl shadow-md" id="studentTable">';
echo '<thead>';
echo '<tr class="bg-gray-100 text-left">';

// Colonnes du tableau avec arrondis
$columns = [
    'full_name' => get_string('full_name', 'mod_studentqcm'),
    'completed_evaluations' => get_string('completed_question', 'mod_studentqcm'),
    'last_connected' => get_string('last_connected', 'mod_studentqcm')
];

$columnIndex = 0;
foreach ($columns as $key => $label) {
    $roundedClass = ($columnIndex == 0) ? 'rounded-tl-3xl' : (($columnIndex == count($columns) - 1) ? 'rounded-tr-3xl' : '');

    echo '<th class="px-3 py-3 text-sm font-medium text-gray-500 uppercase tracking-wider cursor-pointer ' . $roundedClass . '"
              onclick="sortTable(' . $columnIndex . ')">
              ' . mb_strtoupper($label, 'UTF-8');
    echo ' <i class="fas fa-sort ml-2"></i>';
    echo '</th>';
    $columnIndex++;
}

echo '</tr>';
echo '</thead>';
echo '<tbody>';

// Affichage des enseignants
foreach ($teachers as $teacher) {

    $teacher_entity = $DB->get_record('user', array('id' => $teacher->userid));
    $teacher_fullname = ucwords(strtolower($teacher_entity->firstname)) . ' ' . ucwords(strtolower($teacher_entity->lastname));

    echo '<tr class="border-t hover:bg-gray-50">';

    echo '<td class="px-3 py-4 text-md text-gray-600">';
    echo '<div id="name-' . $teacher->userid . '" class="text-gray-600">' . $teacher_fullname . '</div>';
    echo '</td>';

    $colorClass = ($completed_questions_count == 0) ? 'text-red-400' : 
                  (($completed_questions_count == $nbTotal_question) ? 'text-lime-400' : 'text-gray-600');

    echo '<td class="px-3 py-4 text-md ' . $colorClass . '">' . $completed_questions_count . " / " . $nbTotal_question . '</td>';

    echo '<td class="px-3 py-4 text-md text-gray-600">' . 
         ($teacher_entity->lastaccess > 0 
            ? date('d/m/Y', $teacher_entity->lastaccess) 
            : mb_strtoupper(get_string('never_connected', 'mod_studentqcm'), 'UTF-8')) . 
         '</td>';

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

</script>
