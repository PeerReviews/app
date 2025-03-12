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

echo "<div class='bg-gray-100 border-l-4 border-blue-500 p-4 mt-4 rounded-2xl'>";
echo "<div class='flex items-center'>";
echo "<i class='fas fa-info-circle mr-3'></i>";
echo "<p class='text-sm text-gray-600'>" . get_string('information_session', 'mod_studentqcm') . "</p>"; 
echo "</div>";
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
        echo '<a href="edit_session.php?id=' . $id . '&session_id=' . $session->id . '" class="px-4 py-2 min-w-40 bg-indigo-400 hover:bg-indigo-500 text-white text-md font-semibold rounded-2xl text-center" title="Modifier cette session">';
        echo '<i class="fa-solid fa-pen-to-square"></i>';
        echo '</a>';

        echo '<form action="duplicate_session.php?id=' . $id . '" method="post" style="display:inline;">';
            echo '<input type="hidden" name="session_id" value="' . $session->id . '">';
            echo '<button type="submit" class="px-4 py-2 min-w-40 bg-sky-400 hover:bg-sky-500 text-white text-md font-semibold rounded-2xl" title="Dupliquer cette session">';
            echo '<i class="fa-solid fa-clone"></i>';
            echo '</button>';
        echo '</form>';

        if ($session->archived == 1){
            // Formulaire pour rendre cette session courante
            echo '<form action="set_current_session.php?id=' . $id . '&session_id=' . $session->id . '" method="post" style="display:inline;">';
                echo '<input type="hidden" name="session_id" value="' . $session->id . '">';
                echo '<button type="submit" onclick="showActivateModal(' . $session->id . '); event.preventDefault();" class="px-4 py-2 min-w-40 bg-lime-400 hover:bg-lime-500 text-white text-md font-semibold rounded-2xl" title="Rendre cette session active">';
                echo '<i class="fa-solid fa-check"></i>';
                echo '</button>';
            echo '</form>';
        }
        else {
            echo '<form action="archive_session.php?id=' . $id . '" method="post" style="display:inline;">';
                echo '<input type="hidden" name="session_id" value="' . $session->id . '">';
                echo '<button type="submit" class="px-4 py-2 min-w-40 bg-gray-300 hover:bg-gray-400 text-white text-md font-semibold rounded-2xl" title="Archiver cette session">';
                echo '<i class="fa-solid fa-box-archive"></i>';
                echo '</button>';
            echo '</form>';
        }

        echo '<form action="export_session.php?id=' . $id . '" method="post" style="display:inline;">';
                echo '<input type="hidden" name="session_id" value="' . $session->id . '">';
                echo '<button type="submit" class="px-4 py-2 min-w-40 bg-sky-400 hover:bg-sky-500 text-white text-md font-semibold rounded-2xl" title="Exporter l\'ensemble des questions produites lors de cette session">';
                echo '<i class="fa-solid fa-file-zipper"></i>';
                echo '</button>';
        echo '</form>';
        
        echo '<form action="delete_session.php?id=' . $id . '&session_id=' . $session->id .'" method="post" style="display:inline;">';
            echo '<input type="hidden" name="session_id" value="' . $session->id . '">';
            echo '<button type="submit" onclick="showDeleteModal(' . $session->id . '); event.preventDefault();" class="px-4 py-2 min-w-40 bg-red-500 hover:bg-red-600 text-white text-md font-semibold rounded-2xl" title="Supprimer cette session">';
            echo '<i class="fa-solid fa-trash"></i>';
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

<!-- Modal de confirmation de suppression -->
<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
    <div class="bg-white rounded-3xl py-4 px-16 max-w-lg w-full">
        <div class="flex justify-between items-center">
            <h3 class="text-2xl font-semibold text-red-600"><?php echo get_string('message_delete', 'mod_studentqcm'); ?></h3>
            <button id="close-delete-modal" class="text-gray-600 hover:text-gray-800 font-bold text-xl">&times;</button>
        </div>
        <div id="delete-message" class="mt-4 text-gray-700">
            <p><?php echo get_string('confirm_delete_session', 'mod_studentqcm'); ?></p>
        </div>
        <div class="mt-2 text-right">
            <button id="confirm-delete-btn" class="px-4 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 ml-2"><?php echo get_string('delete', 'mod_studentqcm'); ?></button>
        </div>
    </div>
</div>

<!-- Modal de confirmation d'activation -->
<div id="activate-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
    <div class="bg-white rounded-3xl py-4 px-16 max-w-lg w-full">
        <div class="flex justify-between items-center">
            <h3 class="text-2xl font-semibold text-lime-400"><?php echo get_string('message_activate', 'mod_studentqcm'); ?></h3>
            <button id="close-activate-modal" class="text-gray-600 hover:text-gray-800 font-bold text-xl">&times;</button>
        </div>
        <div id="activate-message" class="mt-4 text-gray-700">
            <p><?php echo get_string('confirm_activate_session', 'mod_studentqcm'); ?></p>
        </div>
        <div class="mt-2 text-right">
            <button id="confirm-activate-btn" class="px-4 py-2 bg-lime-400 text-white rounded-full hover:bg-lime-500 ml-2"><?php echo get_string('activate', 'mod_studentqcm'); ?></button>
        </div>
    </div>
</div>

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

    const deleteModal = document.getElementById('delete-modal');
    const closeModalBtn = document.getElementById('close-delete-modal');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');

    let sessionToDeleteId = null;

    function showDeleteModal(sessionId) {
        sessionToDeleteId = sessionId;
        deleteModal.classList.remove('hidden');
    }

    closeModalBtn.addEventListener('click', function () {
        deleteModal.classList.add('hidden');
    });

    confirmDeleteBtn.addEventListener('click', function () {
        if (sessionToDeleteId) {
            // Soumettre le formulaire de suppression
            const form = document.querySelector(`form[action="delete_session.php?id=<?php echo $id; ?>&session_id=${sessionToDeleteId}"]`);
            form.submit();
        }
    });

    const activateModal = document.getElementById('activate-modal');
    const closeModalBtnActivate = document.getElementById('close-activate-modal');
    const confirmActivateBtn = document.getElementById('confirm-activate-btn');

    let sessionToActiveId = null;

    function showActivateModal(sessionId) {
        sessionToActiveId = sessionId;
        activateModal.classList.remove('hidden');
    }

    closeModalBtnActivate.addEventListener('click', function () {
        activateModal.classList.add('hidden');
    });

    confirmActivateBtn.addEventListener('click', function () {
        if (sessionToActiveId) {
            // Soumettre le formulaire de suppression
            const form = document.querySelector(`form[action="set_current_session.php?id=<?php echo $id; ?>&session_id=${sessionToActiveId}"]`);
            form.submit();
        }
    });

</script>