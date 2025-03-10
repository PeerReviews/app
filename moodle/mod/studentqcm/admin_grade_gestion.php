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
              <div class="flex items-center justify-center space-x-2">';

    echo '<p class="w-32 break-words text-center">' . mb_strtoupper($label, 'UTF-8') . '</p>';

    // Ajout du bouton "œil" pour la colonne "Nom complet"
    if ($key === 'full_name') {
        echo ' <button onclick="toggleAllNames()" class="ml-2 px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded-full">';
        echo '<i class="fas fa-eye" id="eye-icon-all"></i></button>';
    }

    echo ' <i class="fas fa-sort ml-2"></i>';
    echo '</div></th>';
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

    // echo '<tr class="border-t hover:bg-gray-50">';

    // echo '<td class="px-3 py-4 text-md text-gray-600">' . $student->userid . '</td>';

    // echo '<td class="px-3 py-4 text-md text-gray-600">';
    // echo '<div id="name-' . $student->userid . '" class="text-gray-600 hidden">' . $student_fullname . '</div>';
    // echo '</td>';

    // // echo '<td class="px-3 py-4 text-md text-gray-600">' . $total_grade_questions . " / " . ($nbTotal_question * 5) .  '</td>';
    // // echo '<td class="px-3 py-4 text-md text-gray-600">' . $total_grade_revisions . " / " . ($nbTotal_revision * 5) .  '</td>';
    
    // // Modification de la colonne $total_grade_questions avec un formulaire
    // echo '<td class="px-3 py-4 text-md text-gray-600">
    // <form action="update_grades.php" method="POST" style="display: inline;">
    //     <input type="hidden" name="studentid" value="' . htmlspecialchars($student->userid) . '">
    //     <input type="number" name="total_grade_questions" value="' . htmlspecialchars($total_grade_questions) . '" class="text-gray-600 w-20 text-center">
    //     / ' . ($nbTotal_question * 5) . '
    //     <button type="submit" class="hidden">Update</button>
    // </form>
    // </td>';

    // // Modification de la colonne $total_grade_revisions avec un formulaire
    // echo '<td class="px-3 py-4 text-md text-gray-600">
    // <form action="update_grades.php" method="POST" style="display: inline;">
    //     <input type="hidden" name="studentid" value="' . htmlspecialchars($student->userid) . '">
    //     <input type="number" name="total_grade_revisions" value="' . htmlspecialchars($total_grade_revisions) . '" class="text-gray-600 w-20 text-center">
    //     / ' . ($nbTotal_revision * 5) . '
    //     <button type="submit" class="hidden">Update</button>
    // </form>
    // </td>';
    
    // echo '<td class="px-3 py-4 text-md text-gray-600">' . $total_general . " / " . ($nbTotal_question * 5 + $nbTotal_revision * 5) .  '</td>';

    // echo '<td class="px-3 py-4 text-md text-gray-600">' . 
    //      ($student_name->lastaccess > 0 
    //         ? date('d/m/Y', $student_name->lastaccess) 
    //         : mb_strtoupper(get_string('never_connected', 'mod_studentqcm'), 'UTF-8')) . 
    //      '</td>';

    // echo '<td class="px-3 py-4 text-md text-gray-600 flex space-x-2">';
    //     echo '<a href="show_production.php?id=' . $id . '&prod_id=' . $student->userid . '" 
    //             class="px-3 py-2 text-white bg-sky-300 hover:bg-sky-400 rounded-lg shadow-md" 
    //             title="' . get_string('show_production', 'mod_studentqcm') . '">
    //             <i class="fas fa-p"></i>
    //         </a>';
 
    //     echo '<a href="show_revisions.php?id=' . $id . '&studentid=' . $student->userid . '" 
    //             class="px-3 py-2 text-white bg-indigo-400 hover:bg-indigo-500 rounded-lg shadow-md" 
    //             title="' . get_string('show_revision', 'mod_studentqcm') . '">
    //             <i class="fas fa-r"></i>
    //         </a>';

    //     echo '<button class="px-3 py-2 text-white bg-lime-400 hover:bg-lime-500 rounded-lg shadow-md edit-button" 
    //             title="' . get_string('show_modification', 'mod_studentqcm') . '" 
    //             data-studentid="' . $student->userid . '">
    //             <i class="fas fa-pen-to-square"></i>
    //         </button>';

    // echo '</td>';

    // echo '</tr>';

    echo '<tr class="border-t hover:bg-gray-50">';

        // Ajout d'un formulaire autour de la ligne entière
        echo '<form action="update_grades.php" method="POST" class="grade-form">';

        echo '<td class="px-3 py-4 text-md text-gray-600">' . $student->userid . '</td>';

        echo '<td class="px-3 py-4 text-md text-gray-600">';
        echo '<div id="name-' . $student->userid . '" class="text-gray-600">' . $student_fullname . '</div>';
        echo '</td>';

        // Champ caché pour l'ID de l'étudiant
        echo '<input type="hidden" name="studentid" value="' . htmlspecialchars($student->userid) . '">';

        // Modification de la colonne $total_grade_questions
        echo '<td class="px-3 py-4 text-md text-gray-600">
            <input type="number" name="total_grade_questions" value="' . htmlspecialchars($total_grade_questions) . '" 
                class="text-gray-600 w-20 text-center grade-input" data-studentid="' . $student->userid . '" disabled>
            / ' . ($nbTotal_question * 5) . '
        </td>';

        // Modification de la colonne $total_grade_revisions
        echo '<td class="px-3 py-4 text-md text-gray-600">
            <input type="number" name="total_grade_revisions" value="' . htmlspecialchars($total_grade_revisions) . '" 
                class="text-gray-600 w-20 text-center grade-input" data-studentid="' . $student->userid . '" disabled>
            / ' . ($nbTotal_revision * 5) . '
        </td>';

        echo '<td class="px-3 py-4 text-md text-gray-600">' . $total_general . " / " . ($nbTotal_question * 5 + $nbTotal_revision * 5) . '</td>';

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

            echo '<button type="button" class="px-3 py-2 text-white bg-lime-400 hover:bg-lime-500 rounded-lg shadow-md edit-button" 
                    title="' . get_string('show_modification', 'mod_studentqcm') . '" 
                    data-studentid="' . $student->userid . '" onclick="toggleEdit(this, ' . $student->userid . ')">
                    <i class="fas fa-pen-to-square"></i>
                </button>';

        echo '</td>';

    // Fermeture du formulaire
    echo '</form>';

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

function saveRow(button) {
    let row = button.closest('tr');
    let inputs = row.querySelectorAll('input:not([disabled])'); // Sélectionne seulement les champs modifiables
    let allFilled = true;

    inputs.forEach(input => {
        // Exclure les champs d'action de la validation
        if (input.name.indexOf('actions') === -1 && !input.value.trim()) {
            allFilled = false;
        }
    });

    if (!allFilled) {
        alert("Tous les champs doivent être remplis !");
        return;
    }

    let gradeId = null;

    // Remplace les champs par leur valeur
    inputs.forEach(input => {
        if (input.name === "grade_id") {
            gradeId = input.value;
        }
        input.parentElement.innerHTML = input.value;
    });

    // Supprime le bouton "Enregistrer" après enregistrement
    let actionsCell = button.closest('td'); // Cellule contenant le bouton "Enregistrer"
    actionsCell.innerHTML = ''; // Vider le contenu de la cellule des actions

    // Créer le bouton "Modifier"
    let editButton = document.createElement("button");
    editButton.innerText = "Modifier";
    editButton.classList.add("px-3", "py-1", "bg-blue-500", "text-white", "rounded");
    
    // Ajouter l'événement pour réactiver les champs et permettre la modification
    editButton.onclick = function() {
        event.preventDefault();
        editRow(editButton, gradeId, row); // Appeler la fonction editRow pour activer la modification des champs
    };

    // Ajouter le bouton "Modifier" dans la cellule d'action
    actionsCell.appendChild(editButton);

    let form = document.getElementById('gradeForm');
    
    inputs.forEach(input => {
        let gradeInput = document.createElement("input");
        gradeInput.type = "hidden"; // Pas visible pour l'utilisateur
        gradeInput.name = `save_grades[${i}][${input.name}]`; // Prend le nom de l'input
        gradeInput.value = input.value; // La valeur de l'input
        form.appendChild(gradeInput);
    });
}

let updatedGrades = [];

function editRow(button, gradeId, row = null) {
    if (row == null) {
        row = document.getElementById("row-" + gradeId);
    }
    let cells = row.querySelectorAll("td");

    if (button.innerText === "Modifier") {
        // Activer les champs de saisie pour modifier les grades
        cells.forEach((cell, index) => {
            if (index === 2 || index === 3) { // Par exemple, les cellules des grades
                let cellContent = cell.innerHTML.trim();
                let input = document.createElement("input");
                input.type = "number";
                input.value = cellContent;
                input.name = "grade" + (index - 1);
                cell.innerHTML = '';
                cell.appendChild(input);
            }
        });

        button.innerText = "Enregistrer";
    } else {

        let gradeData = { studentId: studentId };
        let allFilled = true;

        let inputs = row.querySelectorAll("input"); 
        inputs.forEach((input) => {
            let value = input.value.trim();
            
            if (input.name === "student_id") {
                gradeData.studentId = value;
                if (value === "") {
                    allFilled = false;
                }
            } else if (input.name === "grade_1" || input.name === "grade_2") {
                gradeData[input.name] = value === "" ? null : value; // Met null si vide
                if (value === "") {
                    allFilled = false;
                }
            } 
        });

        // Vérifier si les champs obligatoires sont remplis
        if (!allFilled) {
            alert("Tous les champs doivent être remplis !");
            return;
        }

        if (Object.keys(gradeData).length > 1) {
            let index = updatedGrades.findIndex(data => data.gradeId === gradeId);
            if (index !== -1) {
                updatedGrades[index] = gradeData;  // Mettre à jour si trouvé
            } else {
                updatedGrades.push(gradeData);  // Ajouter s'il n'existe pas
            }
        }

        // Remettre les valeurs modifiées dans les cellules
        cells.forEach((cell, index) => {
            let input = cell.querySelector("input");
            if (input) {
                cell.innerHTML = input.value;
            }
        });

        button.innerText = "Modifier";
    }
}

// document.addEventListener("DOMContentLoaded", function () {
//     document.querySelectorAll("input[type='number']").forEach(input => {
//         input.addEventListener("change", function () {
//             let form = this.closest("form");
//             updateGrade(form);
//         });
//     });
// });

// function updateGrade(form) {
//     let formData = new FormData(form);
    
//     fetch("edit_grade.php", {
//         method: "POST",
//         body: formData
//     })
//     .then(response => response.json())
//     .then(data => {
//         if (data.success) {
//             console.log("Mise à jour réussie pour l'étudiant ID " + formData.get("studentid"));
//         } else {
//             console.error("Erreur lors de la mise à jour :", data.message);
//         }
//     })
//     .catch(error => console.error("Erreur de requête :", error));
// }

document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".edit-button").forEach(button => {
        button.addEventListener("click", function () {
            let studentId = this.dataset.studentid;
            let inputs = document.querySelectorAll(`input[data-studentid='${studentId}']`);

            inputs.forEach(input => {
                input.removeAttribute("readonly"); // Rend le champ modifiable
                input.classList.add("border", "border-gray-400", "bg-white"); // Ajoute un style visuel
            });

            console.log(`Champs éditables pour l'étudiant ID ${studentId}`);
        });
    });

    document.querySelectorAll(".grade-input").forEach(input => {
        input.addEventListener("change", function () {
            let studentId = this.dataset.studentid;
            let fieldName = this.dataset.field;
            let fieldValue = this.value;

            updateGrade(studentId, fieldName, fieldValue);
        });
    });
});

function updateGrade(studentId, fieldName, fieldValue) {
    let formData = new FormData();
    formData.append("studentid", studentId);
    formData.append(fieldName, fieldValue);

    fetch("edit_grade.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log(`Mise à jour réussie pour l'étudiant ID ${studentId}`);
        } else {
            console.error("Erreur lors de la mise à jour :", data.message);
        }
    })
    .catch(error => console.error("Erreur de requête :", error));
}


</script>
