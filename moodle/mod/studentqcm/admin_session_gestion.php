<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

// Récupération du module, cours et QCM
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/admin_grade_gestion.php', array('id' => $id));
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
echo '<table class="min-w-full bg-white rounded-3xl shadow-md" id="studentTable">';
echo '<thead>';
echo '<tr class="bg-gray-100 text-left">';

// Colonnes du tableau avec arrondis
$columns = [
    'session_id' => get_string('session_id', 'mod_studentqcm'),
    'session_name' => get_string('session_name', 'mod_studentqcm'),
    'actions' => get_string('actions', 'mod_studentqcm')
];

$columnIndex = 0;
foreach ($columns as $key => $label) {
    $roundedClass = ($columnIndex == 0) ? 'rounded-tl-3xl' : (($columnIndex == count($columns) - 1) ? 'rounded-tr-3xl' : '');

    echo '<th class="w-1/3 px-3 py-3 text-sm font-medium text-gray-500 uppercase tracking-wider cursor-pointer ' . $roundedClass . '"
              onclick="sortTable(' . $columnIndex . ')">
              ' . mb_strtoupper($label, 'UTF-8');

    echo ' <i class="fas fa-sort ml-2"></i>';
    echo '</th>';
    $columnIndex++;
}
echo '</tr>';
echo '</thead>';
echo '<tbody>';

// Affichage des étudiants
foreach ($sessions as $session) {

    $session_id = $session->id;
    $session_name = ucwords(strtolower($session->name));

    echo '<tr class="border-t hover:bg-gray-50">';

    echo '<td class="w-1/3 px-3 py-4 text-md text-gray-600">' . $session_id . '</td>';

    echo '<td class="w-1/3 px-3 py-4 text-md text-gray-600">' . $session_name . '</div>';

    echo '<td class="w-1/3 px-2 py-4 text-md text-gray-600">';
    echo "<a href='#' class='mx-2 px-3 py-2 bg-sky-400 text-white rounded-lg hover:bg-sky-500'>";
    echo '<i class="fas fa-pen-to-square"></i>';
    echo '</a>';

    echo "<a href='#' class='mx-2 px-3 py-2 bg-indigo-500 text-white rounded-lg hover:bg-indigo-600' onclick='duplicateSession({$session->id}); return false;>";
    echo '<i class="fas fa-solid fa-copy"></i>';
    echo '</a>';

    echo "<a href='#' class='mx-2 px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600' onclick='deleteSession({$session->id}); return false;'>";
    echo '<i class="fas fa-trash"></i>';
    echo '</a>';
    echo '</td>';

    echo '</tr>';
}

echo '</tbody>';
echo '</table>';
echo '</div>';
?>

<!-- Modal de confirmation de suppression -->
<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
    <div class="bg-white rounded-3xl py-4 px-16 max-w-lg w-full">
        <div class="flex justify-between items-center">
            <h3 class="text-2xl font-semibold text-red-600"><?php echo get_string('message_delete', 'mod_studentqcm'); ?></h3>
            <button id="close-delete-modal" class="text-gray-600 hover:text-gray-800 font-bold text-xl">&times;</button>
        </div>
        <div id="delete-message" class="mt-4 text-gray-700">
            <p><?php echo get_string('confirm_delete', 'mod_studentqcm'); ?></p>
        </div>
        <div class="mt-2 text-right">
            <button id="confirm-delete-btn" class="px-4 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 ml-2"><?php echo get_string('delete', 'mod_studentqcm'); ?></button>
        </div>
    </div>
</div>

<?php
echo $OUTPUT->footer();
?>

<script>
    const deleteModal = document.getElementById('delete-modal');
    const closeModalBtn = document.getElementById('close-delete-modal');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');

    let sessionToDeleteId = null;

    function deleteSession(session_id){
        console.log("in delete session");
        sessionToDeleteId = session_id;
        deleteModal.classList.remove('hidden');
    }

    closeModalBtn.addEventListener('click', function () {
        deleteModal.classList.add('hidden');
    });

    confirmDeleteBtn.addEventListener('click', function () {
        if (sessionToDeleteId) {
            window.location.href = `session_delete.php?id=${<?php echo $id; ?>}&session_id=${sessionToDeleteId}`;
        }
    });

    function duplicateSession(session_id) {
        if (session_id) {
            window.location.href = `session_duplicate.php?id=${<?php echo $id; ?>}&session_id=${session_id}`;
        }
    }
</script>