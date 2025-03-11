<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

// Récupération du module, cours 
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/admin_sessions.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('session_list', 'mod_studentqcm') . "</p>";
echo "</div>";

// Bouton retour
echo "<div class='flex mt-8 text-lg justify-between'>";
echo "<a href='view.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 text-gray-500 no-underline'>";
echo "<i class='fas fa-arrow-left mr-2'></i>";
echo get_string('back', 'mod_studentqcm');
echo "</a>";
echo "</div>"; 

// Récupération des sessions
$sessions = $DB->get_records('studentqcm');

echo '<div class="mt-8">';


echo '<table class="min-w-full bg-white rounded-3xl shadow-md" id="table">';
echo '<thead>';
echo '<tr class="bg-gray-100 text-left">';

$columns = [
    'session_name' => get_string('session_name', 'mod_studentqcm'),
    // 'session_start' => get_string('session_start', 'mod_studentqcm'),
    // 'session_end' => get_string('session_end', 'mod_studentqcm'),
    'time_created' => get_string('time_created', 'mod_studentqcm'),
    'archived' => get_string('archived', 'mod_studentqcm'),
    'actions' => get_string('actions', 'mod_studentqcm')
];

$columnIndex = 0;
foreach ($columns as $key => $label) {
    $roundedClass = ($columnIndex == 0) ? 'rounded-tl-3xl' : (($columnIndex == count($columns) - 1) ? 'rounded-tr-3xl' : '');

    echo '<th class="px-3 py-3 text-sm font-medium text-gray-500 uppercase tracking-wider cursor-pointer ' . $roundedClass . '"
            onclick="sortTable(' . $columnIndex . ', \'table\')">
            ' . mb_strtoupper($label, 'UTF-8');

    echo ' <i class="fas fa-sort ml-2"></i>';
    echo '</th>';
    $columnIndex++;
}

echo '</tr>';
echo '</thead>';

echo '<tbody id="tableBody">';

// Affichage des étudiants
foreach ($sessions as $session) {

    $row_class = $session->archived == 1 ? 'bg-indigo-50 hover:bg-indigo-100' : 'hover:bg-gray-50';

    echo '<tr id="row-' . $session->id . '" class="border-t ' . $row_class . '">';
    echo '<td class="px-3 py-4 text-md text-gray-600">' . $session->name . '</td>';
    echo '<td class="px-3 py-4 text-md text-gray-600">' . date('d/m/Y', $session->timecreated) . '</td>';
    echo '<td class="px-3 py-4 text-md text-gray-600">' . ($session->archived == 1 ? 'Oui' : 'Non') . '</td>';

    echo '<td class="p-4 text-md text-gray-600 flex items-center space-x-2">';
        echo '<a href="edit_session.php?id=' . $id . '&session_id=' . $session->id . '" class="px-4 py-2 min-w-40 bg-indigo-400 hover:bg-indigo-500 text-white text-md font-semibold rounded-2xl text-center">';
        echo get_string('edit', 'mod_studentqcm');
        echo '</a>';

        echo '<form action="duplicate_session.php?id=' . $id . '" method="post" style="display:inline;">';
            echo '<input type="hidden" name="session_id" value="' . $session->id . '">';
            echo '<button type="submit" class="px-4 py-2 min-w-40 bg-lime-400 hover:bg-lime-500 text-white text-md font-semibold rounded-2xl">';
            echo get_string('duplicate', 'mod_studentqcm');
            echo '</button>';
        echo '</form>';

        if ($session->archived == 1){
            // Formulaire pour rendre cette session courante
            echo '<form action="set_current_session.php?id=' . $id . '" method="post" style="display:inline;">';
                echo '<input type="hidden" name="session_id" value="' . $session->id . '">';
                echo '<button type="submit" class="px-4 py-2 min-w-40 bg-sky-400 hover:bg-sky-500 text-white text-md font-semibold rounded-2xl">';
                echo get_string('set_current_session', 'mod_studentqcm');
                echo '</button>';
            echo '</form>';
        }
        else {
            echo '<form action="archive_session.php?id=' . $id . '" method="post" style="display:inline;">';
                echo '<input type="hidden" name="session_id" value="' . $session->id . '">';
                echo '<button type="submit" class="px-4 py-2 min-w-40 bg-sky-400 hover:bg-sky-500 text-white text-md font-semibold rounded-2xl">';
                echo get_string('archive', 'mod_studentqcm');
                echo '</button>';
            echo '</form>';
        }

        echo '<form action="delete_session.php?id=' . $id . '" method="post" onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer cette session ?\nCette action est irréversible.\')" style="display:inline;">';
            echo '<input type="hidden" name="session_id" value="' . $session->id . '">';
            echo '<button type="submit" class="px-4 py-2 min-w-40 bg-red-500 hover:bg-red-600 text-white text-md font-semibold rounded-2xl">';
            echo get_string('delete', 'mod_studentqcm');
            echo '</button>';
        echo '</form>';
    echo '</td>';

    echo '</tr>';
}

echo '</tbody>';
echo '</table>';

echo '</div>';


echo $OUTPUT->footer();
?>

<script>
function sortTable(columnIndex, table) {
    var table = document.getElementById(table);
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

// // Soumettre le formulaire manuellement
// document.addEventListener('DOMContentLoaded', () => {
//     document.getElementById('saveButton').addEventListener('click', function() {
        
//         document.querySelectorAll('input[name="updatedData[]"]').forEach(input => {
//             if (input.value.trim() === "" || input.value === "{}") {
//                 input.remove();
//             }
//         });

//         // Vérifier que `updatedData` contient uniquement des entrées valides
//         updatedData = updatedData.filter(item => Object.keys(item).length > 1);

//         // Ajouter les données mises à jour au formulaire
//         updatedData.forEach(item => {
//             let input = document.createElement("input");
//             input.type = "hidden";
//             input.name = "updatedData[]";  // Si plusieurs étudiants sont envoyés
//             input.value = JSON.stringify(item);
//             document.getElementById("sessionForm").appendChild(input);
//         });

//         // Soumettre le formulaire
//         let form = document.getElementById('sessionForm');
//         if (form) {
//             form.submit();
//         } else {
//             console.error("Formulaire 'sessionForm' introuvable !");
//         }
//     });
// });

</script>