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
echo '<form id="grade-form" action="edit_grade.php?id=' . $id . '" method="POST" class="grade-form">';
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
    // $sql_questions = "SELECT SUM(grade) as total_grade 
    //     FROM {studentqcm_question} 
    //     WHERE userid = ? AND status = 1";
    // $total_grade_questions = $DB->get_field_sql($sql_questions, array($student->userid));
    // $total_grade_questions = $total_grade_questions !== null ? $total_grade_questions : 0;

    // // Récupérer la somme des notes des révisions de l'étudiant
    // $sql_revisions = "SELECT SUM(grade) as total_grade 
    //     FROM {studentqcm_evaluation} 
    //     WHERE userid = ? AND status = 1";
    // $total_grade_revisions = $DB->get_field_sql($sql_revisions, array($student->userid));
    // $total_grade_revisions = $total_grade_revisions !== null ? $total_grade_revisions : 0;

    // Production_grade

    $record = $DB->get_record('pr_grade', ['userid' => $student->userid], '*');
    $total_grade_questions = $record ? intval($record->production_grade) : 0;

    // Revision_grade

    $record = $DB->get_record('pr_grade', ['userid' => $student->userid], '*');
    $total_grade_revisions = $record ? intval($record->revision_grade) : 0;

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

    // AVANT
    //     echo '<form action="edit_grade.php" method="POST" class="grade-form">';


    //     echo '<td class="px-3 py-4 text-md text-gray-600">' . $student->userid . '</td>';

    //     echo '<td class="px-3 py-4 text-md text-gray-600">';
    //     echo '<div id="name-' . $student->userid . '" class="text-gray-600">' . $student_fullname . '</div>';
    //     echo '</td>';

    //     // Modification de la colonne $total_grade_questions
    //     echo '<td class="px-3 py-4 text-md text-gray-600">
    //         <input type="number" name="total_grade_questions" value="' . htmlspecialchars($total_grade_questions) . '" 
    //             class="text-gray-600 w-20 text-center grade-input" data-studentid="' . $student->userid . '" disabled>
    //         / ' . ($nbTotal_question * 5) . '
    //     </td>';

    //     // Modification de la colonne $total_grade_revisions
    //     echo '<td class="px-3 py-4 text-md text-gray-600">
    //         <input type="number" name="total_grade_revisions" value="' . htmlspecialchars($total_grade_revisions) . '" 
    //             class="text-gray-600 w-20 text-center grade-input" data-studentid="' . $student->userid . '" disabled>
    //         / ' . ($nbTotal_revision * 5) . '
    //     </td>';


    //     echo '<td class="px-3 py-4 text-md text-gray-600">' . $total_general . " / " . ($nbTotal_question * 5 + $nbTotal_revision * 5) . '</td>';

    //     echo '<td class="px-3 py-4 text-md text-gray-600">' . 
    //         ($student_name->lastaccess > 0 
    //             ? date('d/m/Y', $student_name->lastaccess) 
    //             : mb_strtoupper(get_string('never_connected', 'mod_studentqcm'), 'UTF-8')) . 
    //         '</td>';

    //     echo '<td class="px-3 py-4 text-md text-gray-600 flex space-x-2">';
    //         echo '<a href="show_production.php?id=' . $id . '&prod_id=' . $student->userid . '" 
    //                 class="px-3 py-2 text-white bg-sky-300 hover:bg-sky-400 rounded-lg shadow-md" 
    //                 title="' . get_string('show_production', 'mod_studentqcm') . '">
    //                 <i class="fas fa-p"></i>
    //             </a>';

    //         echo '<a href="show_revisions.php?id=' . $id . '&studentid=' . $student->userid . '" 
    //                 class="px-3 py-2 text-white bg-indigo-400 hover:bg-indigo-500 rounded-lg shadow-md" 
    //                 title="' . get_string('show_revision', 'mod_studentqcm') . '">
    //                 <i class="fas fa-r"></i>
    //             </a>';

    //         echo '<button type="button" class="px-3 py-2 text-white bg-lime-400 hover:bg-lime-500 rounded-lg shadow-md edit-button" 
    //                 title="' . get_string('show_modification', 'mod_studentqcm') . '" 
    //                 data-studentid="' . $student->userid . '" onclick="toggleEdit(this, ' . $student->userid . ')">
    //                 <i class="fas fa-pen-to-square"></i>
    //             </button>';

    //     echo '</td>';

    //     // Champ caché pour l'ID de l'étudiant, placé après les champs visibles
    //     echo '<input type="hidden" name="updatedData[]" value="' . json_encode([
    //         'studentid' => $student->userid,
    //         'total_grade_questions' => $total_grade_questions,
    //         'total_grade_revisions' => $total_grade_revisions
    //     ]) . '">';

    //     echo '</form>';
    echo '<tr id="row-' . $student->userid . '" class="border-t hover:bg-gray-50">';

        echo '<td class="px-3 py-4 text-md text-gray-600">' . $student->userid . '</td>';

        echo '<td class="px-3 py-4 text-md text-gray-600">';
        echo '<div id="name-' . $student->userid . '" class="text-gray-600">' . $student_fullname . '</div>';
        echo '</td>';

        // Modification de la colonne $total_grade_questions
        echo '<td class="px-3 py-4 text-md text-gray-600">
            <input type="number" name="total_grade_questions" value="' . htmlspecialchars($total_grade_questions) . '" 
                class="text-gray-600 w-20 text-center grade-input" data-studentid="' . $student->userid . '" id="total_grade_questions-' . $student->userid . '" disabled>
            / ' . ($nbTotal_question * 5) . '
        </td>';

        // Modification de la colonne $total_grade_revisions
        echo '<td class="px-3 py-4 text-md text-gray-600">
            <input type="number" name="total_grade_revisions" value="' . htmlspecialchars($total_grade_revisions) . '" 
                class="text-gray-600 w-20 text-center grade-input" data-studentid="' . $student->userid . '" id="total_grade_revisions-' . $student->userid . '" disabled>
            / ' . ($nbTotal_revision * 5) . '
        </td>';

        echo '<input type="hidden" name="updatedData[]" id="updatedData-' . $student->userid . '" value="">';

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
                    data-studentid="' . $student->userid . '" onclick="editRow(this, ' . $student->userid . ')">
                    <i class="fas fa-pen-to-square"></i>
                </button>';

        echo '</td>';

    
    echo '</tr>';
    

}
echo '</form>';
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

// function updateHiddenField(studentId) {
//     const totalGradeQuestionsInput = document.getElementById('total_grade_questions-' + studentId);
//     const totalGradeRevisionsInput = document.getElementById('total_grade_revisions-' + studentId);

//     // Récupérer les nouvelles valeurs des champs de saisie
//     const totalGradeQuestions = totalGradeQuestionsInput.value;
//     const totalGradeRevisions = totalGradeRevisionsInput.value;

//     // Mettre à jour le champ caché avec les nouvelles valeurs
//     const updatedDataField = document.getElementById('updatedData-' + studentId);
//     updatedDataField.value = JSON.stringify({
//         'studentid': studentId,
//         'total_grade_questions': totalGradeQuestions,
//         'total_grade_revisions': totalGradeRevisions
//     });
// }

// function toggleEdit(button, studentId) {
//     const totalGradeQuestionsInput = document.getElementById('total_grade_questions-' + studentId);
//     const totalGradeRevisionsInput = document.getElementById('total_grade_revisions-' + studentId);
//     const form = document.getElementById('grade-form-' + studentId);

//     if (button.textContent.trim() === "Modifier") {
//         totalGradeQuestionsInput.disabled = false;
//         totalGradeRevisionsInput.disabled = false;

//         button.innerHTML = "<i class='fas fa-save'></i>";
//         button.classList.remove('bg-lime-400');
//         button.classList.add('bg-green-500');
//     } else {
//         totalGradeQuestionsInput.disabled = true;
//         totalGradeRevisionsInput.disabled = true;

//         button.innerHTML = "<i class='fas fa-pen-to-square'></i>";
//         button.classList.remove('bg-green-500');
//         button.classList.add('bg-lime-400');

//         // Mettre à jour le champ caché avant de soumettre le formulaire
//         updateHiddenField(studentId);

//         form.submit();
//     }
// }

let updatedData = [];

function editRow(button, studentId) {

    let row = document.getElementById("row-" + studentId);
    let cells = row.querySelectorAll("td");

    if (button.classList.contains("edit-button")) {
        // Activer les champs de saisie pour modifier les produits
        cells.forEach((cell, index) => {
            if (index === 2 || index === 3) {
                let input = cell.querySelector("input");
                if (input) {
                    input.type = "number";
                    input.value = cell.innerHTML.trim();
                    input.name = "grade" + (index - 1);
                    input.removeAttribute("disabled"); // Rendre éditable
                    cell.prepend(input);
                }
            
            }
        });

        button.innerHTML = "<i class='fas fa-save'></i>";
        button.classList.remove("edit-button");
        button.classList.add("save-button");
    } else {

        let gradeData = { studentId: studentId };
        let allFilled = true;

        let inputs = row.querySelectorAll("input"); 
        inputs.forEach((input) => {
            let value = input.value.trim();

            if (input.name === "grade1" || input.name === "grade2") {
                gradeData[input.name] = value === "" ? null : value; // Met null si vide
                if (value === "") {
                    allFilled = false;
                }
            }
        });

        // Vérifier si les champs obligatoires sont remplis
        if (!allFilled) {
            alert("Tous les champs modifiables doivent être remplis !");
            return;
        }

        if (Object.keys(gradeData).length > 1) {
            let index = updatedData.findIndex(data => data.studentId === studentId);
            if (index !== -1) {
                updatedData[index] = gradeData;  // Mettre à jour si trouvé
            } else {
                updatedData.push(gradeData);  // Ajouter s'il n'existe pas
            }
        }

        // Vérifier que `updatedData` contient uniquement des entrées valides
        updatedData = updatedData.filter(item => Object.keys(item).length > 1);

        // Ajouter les données mises à jour au formulaire
        updatedData.forEach(item => {
            let input = document.createElement("input");
            input.type = "hidden";
            input.name = "updatedData[]";  // Si plusieurs étudiants sont envoyés
            input.value = JSON.stringify(item);
            document.getElementById("grade-form").appendChild(input);
        });

        // Supprimer les entrées cachées invalides
        document.querySelectorAll('input[name="updatedData[]"]').forEach(input => {
            if (input.value.trim() === "" || input.value === "{}") {
                input.remove();
            }
        });

        // Soumettre le formulaire manuellement
        let form = document.getElementById('grade-form');
        if (form) {
            form.submit();
        } else {
            console.error("Formulaire 'grade-form' introuvable !");
        }

        // // Ajouter gradeData uniquement si grade1 ou grade2 est rempli
        // if (gradeData.grade1 !== null || gradeData.grade2 !== null) {
        //     let index = updatedData.findIndex(data => data.studentId === studentId);
        //     if (index !== -1) {
        //         updatedData[index] = gradeData;  // Mettre à jour si trouvé
        //     } else {
        //         updatedData.push(gradeData);  // Ajouter s'il n'existe pas
        //     }
        // }

        //  // Mettre à jour ou ajouter les données modifiées
        // let form = document.getElementById("grade-form");
        // for (const key in gradeData) {
        //     if (gradeData.hasOwnProperty(key)) {
        //         let hiddenInput = document.createElement("input");
        //         hiddenInput.type = "hidden";
        //         hiddenInput.name = key;
        //         hiddenInput.value = gradeData[key];
        //         form.appendChild(hiddenInput);
        //     }
        // }

        // // Soumettre le formulaire à "edit_grade.php"
        // form.submit();

        // Remettre les valeurs modifiées dans les cellules
        cells.forEach((cell, index) => {
            let input = cell.querySelector("input");
            if (input) {
                cell.innerHTML = input.value;
            }
        });

        button.innerHTML = "<i class='fas fa-pen-to-square'></i>";
        button.classList.remove("save-button");
        button.classList.add("edit-button");
    }
}

// function saveRow(button) {
//     let row = button.closest('tr');
//     let inputs = row.querySelectorAll('input:not([disabled])'); // Sélectionne seulement les champs modifiables
//     let allFilled = true;

//     inputs.forEach(input => {
//         // Exclure full_name et prod3 de la validation
//         if ((input.name.indexOf('full_name') === -1 && input.name.indexOf('prod3') === -1 && input.name.indexOf('actions') === -1) && !input.value.trim()) {
//             allFilled = false;
//         }
//     });

//     if (!allFilled) {
//         alert("Les champs 'Student ID', 'Production 1' et 'Production 2' doivent être remplis !");
//         return;
//     }

//     let studentId = null;

//     // Remplace les champs par leur valeur
//     inputs.forEach(input => {
//         if (input.name === "student_id"){
//             studentId = input.value;
//         }
//         input.parentElement.innerHTML = input.value;
//     });

//     // Supprime le bouton "Enregistrer" après enregistrement
//     let actionsCell = button.closest('td'); // Cellule contenant le bouton "Enregistrer"
//     actionsCell.innerHTML = ''; // Vider le contenu de la cellule des actions

//     // Créer le bouton "Modifier"
//     let editButton = document.createElement("button");
//     editButton.innerText = "Modifier";
//     editButton.classList.add("px-3", "py-1", "bg-blue-500", "text-white", "rounded");
    
//     // Ajouter l'événement pour réactiver les champs et permettre la modification
//     editButton.onclick = function() {
//         event.preventDefault();
//         editRow(editButton, studentId, row); // Appeler la fonction editRow pour activer la modification des champs
//     };

//     // Ajouter le bouton "Modifier" dans la cellule d'action
//     actionsCell.appendChild(editButton);

//     let form = document.getElementById('grade-form');
    
//     inputs.forEach(input => {
//         let studentInput = document.createElement("input");
//         studentInput.type = "hidden"; // Pas visible pour l'utilisateur
//         studentInput.name = `save_students[${i}][${input.name}]`; // Prend le nom de l'input
//         studentInput.value = input.value; // La valeur de l'input
//         form.appendChild(studentInput);
//     });

// }

</script>