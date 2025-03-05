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

$PAGE->set_url('/mod/studentqcm/admin_add_user.php', array('id' => $id));
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
$students = $DB->get_records('students');

echo '<div class="mt-8">';
echo '<table class="min-w-full bg-white rounded-3xl shadow-md" id="studentTable">';
echo '<thead>';
echo '<tr class="bg-gray-100 text-left">';

// Colonnes du tableau avec arrondis
$columns = [
    'student_id' => get_string('student_id', 'mod_studentqcm'),
    'full_name' => get_string('full_name', 'mod_studentqcm'),
    'tier_temps' => get_string('tier_temps', 'mod_studentqcm'),
    'last_connected' => get_string('last_connected', 'mod_studentqcm'),
    'actions' => get_string('actions', 'mod_studentqcm')
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

// Affichage des étudiants
foreach ($students as $student) {
    $completed_questions_count = $DB->count_records('studentqcm_question', array('userid' => $student->userid, 'status' => 1));

    $student_name = $DB->get_record('user', array('id' => $student->userid));
    $student_fullname = ucwords(strtolower($student_name->firstname)) . ' ' . ucwords(strtolower($student_name->lastname));

    echo '<tr class="border-t hover:bg-gray-50">';

    echo '<td class="px-3 py-4 text-md text-gray-600">' . $student->userid . '</td>';

    echo '<td class="px-3 py-4 text-md text-gray-600">' . $student_fullname . '</div>';

    echo '<td class="px-3 py-4 text-md text-gray-600">';
    
    if ($student->istiertemps) {
        echo '<i class="fas fa-clock text-sky-500"></i>';
    } else {
        echo '<i class="fas fa-xmark text-yellow-500"></i>';
    }
    
    echo '</td>';

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

echo '<div class="mt-8 p-4 bg-indigo-50 rounded-3xl">';
    echo '<p class="font-bold text-center text-2xl text-indigo-400">' . get_string('add_student', 'mod_studentqcm') . '</p>';

    echo "<form method='post' action='admin_add_user.php?id={$id}' class='space-y-5'>";
        echo '<input type="hidden" name="id" value="' . $id . '">';

        echo '<div class="flex gap-4">';
            echo '<input type="text" name="firstname" required placeholder="' . get_string('firstname', 'core') . '" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400">';
            echo '<input type="text" name="lastname" required placeholder="' . get_string('lastname', 'core') . '" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400">';
        echo '</div>';

        echo '<div class="flex gap-4 w-full">';
            echo '<div class="w-full">';
            echo '<input type="email" name="email" required placeholder="' . get_string('email', 'mod_studentqcm') . '" class="w-full px-4 py-2 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400">';
            echo '</div>';

            echo '<div class="w-full py-2 ml-2">';
            echo '<div class="w-full flex items-center gap-2">';
                echo '<label class="font-semibold text-gray-400 text-lg">' . get_string('tier_temps', 'mod_studentqcm') . ' ?</label>';
                echo '<label class="relative inline-flex items-center cursor-pointer">';
                    echo '<input type="checkbox" name="istiertemps" value="1" class="sr-only peer">';
                    echo '<span class="w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-indigo-400 peer-checked:after:translate-x-full peer-checked:after:bg-white 
                                after:content-\'\' after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 
                                after:rounded-full after:h-5 after:w-5 after:transition-all"></span>';
                echo '</label>';
            echo '</div>';
            echo '</div>';

        echo '</div>';

    // Bouton d'ajout
    echo '<div class="text-center">';
    echo '<button type="submit" name="add_student" class="w-full px-6 py-2 bg-indigo-400 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-500 transition">';
    echo '<i class="fas fa-user-plus mr-2"></i>' . get_string('add', 'mod_studentqcm');
    echo '</button>';
    echo '</div>';

echo '</form>';
echo '</div>';


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

document.addEventListener("DOMContentLoaded", function () {
    console.log("JS chargé !");
    
    let form = document.querySelector("form");

    if (!form) {
        console.error("⚠️ Formulaire introuvable !");
        return;
    } else {
        console.log("✅ Formulaire trouvé !");
    }

    // Ajouter un événement directement sur le formulaire
    form.addEventListener("submit", function (e) {
        e.preventDefault();  // Empêche la soumission classique du formulaire
        console.log("🚀 Formulaire soumis !");

        // Récupérer les données du formulaire
        let formData = new FormData(form);
        console.log("📦 Données envoyées :", Object.fromEntries(formData));

        // Envoi des données via fetch
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Vérifier si la réponse est bien JSON
            return response.json();
        })
        .then(data => {
            console.log("📩 Réponse du serveur :", data);
            if (data.success) {
                alert("L'étudiant a été ajouté avec succès !");
                location.reload();  // Recharge la page après ajout
            } else {
                alert("Erreur : " + data.message);
            }
        })
        .catch(error => {
            console.error("❌ Erreur lors de l'envoi :", error);
            alert("Une erreur est survenue. Veuillez réessayer.");
        });
    });
});




</script>


