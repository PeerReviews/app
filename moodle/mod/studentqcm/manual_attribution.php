<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$gestion_type = optional_param('gestion', 'student', PARAM_ALPHA); // Par défaut, gestion des étudiants

$session = $DB->get_record('studentqcm', ['archived' => 0], '*', MUST_EXIST);

// Récupération du module, cours 
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/manual_attribution.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('attribution_list', 'mod_studentqcm') . "</p>";
echo "</div>";

// Bouton retour
echo "<div class='flex mt-8 text-lg justify-between'>";
echo "<a href='view.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 text-gray-500 no-underline'>";
echo "<i class='fas fa-arrow-left mr-2'></i>";
echo get_string('back', 'mod_studentqcm');
echo "</a>";
echo "</div>";

// Gestion étudiant/enseignant
echo "<div class='flex mt-8 text-lg justify-between gap-4'>";
    echo '<a href="?id=' . $id . '&gestion=student" class="w-full p-4 bg-sky-300 text-white font-semibold rounded-2xl shadow-md hover:bg-sky-400 transition">';
    echo '<i class="fa-solid fa-user-graduate mr-2"></i>   ' . get_string('student_gestion', 'mod_studentqcm');
    echo '</a>';

    echo '<a href="?id=' . $id . '&gestion=teacher" class="w-full p-4 bg-sky-300 text-white font-semibold rounded-2xl shadow-md hover:bg-sky-400 transition">';
    echo '<i class="fa-solid fa-user-tie mr-2"></i>   ' . get_string('teacher_gestion', 'mod_studentqcm');
    echo '</a>';
echo "</div>";

if ($gestion_type === 'student') {

    echo "<div class='flex mt-8 text-lg justify-center gap-4'>";
    // Bouton pour déclencher manuellement l'attribution automatique
    echo '<button onclick="triggerStudentAttribution()" class="block px-4 py-2 font-semibold rounded-2xl bg-indigo-300 hover:bg-indigo-400 text-white no-underline">';
    echo '<i class="fa-solid fa-sync mr-2"></i>' . get_string('trigger_attribution', 'mod_studentqcm') . '</button>';

    // Bouton pour ajouter une nouvelle ligne
    echo '<button onclick="addRow()" class="inline-block px-4 py-2 font-semibold rounded-2xl bg-indigo-300 hover:bg-indigo-400 text-white no-underline">';
    echo '<i class="fa-solid fa-plus mr-2"></i>' . get_string('add_attribution', 'mod_studentqcm') . '</button>';
    
    echo "</div>";

    // Récupération des étudiants
    $students = $DB->get_records('studentqcm_assignedqcm', ['sessionid' => $session->id]);

    echo '<div class="mt-8">';

    // Formulaire pour enregistrer les nouvelles lignes
    echo "<form id='attributionForm' method='post' action='submit_attribution.php?id={$id}'>";

    echo '<table class="min-w-full bg-white rounded-3xl shadow-md" id="studentTable">';
    echo '<thead>';
    echo '<tr class="bg-gray-100 text-left">';

    $columns = [
        'student_id' => get_string('student_id', 'mod_studentqcm'),
        'full_name' => get_string('full_name', 'mod_studentqcm'),
        'prod1' => get_string('prod1', 'mod_studentqcm'),
        'prod2' => get_string('prod2', 'mod_studentqcm'),
        'prod3' => get_string('prod3', 'mod_studentqcm'),
        'actions' => get_string('actions', 'mod_studentqcm')
    ];

    $columnIndex = 0;
    foreach ($columns as $key => $label) {
        $roundedClass = ($columnIndex == 0) ? 'rounded-tl-3xl' : (($columnIndex == count($columns) - 1) ? 'rounded-tr-3xl' : '');

        echo '<th class="px-3 py-3 text-sm font-medium text-gray-500 uppercase tracking-wider cursor-pointer ' . $roundedClass . '"
                onclick="sortTable(' . $columnIndex . ', \'studentTable\')">
                <div class="flex items-center justify-center space-x-2">';

                echo '<p class="w-36 break-words text-center">' . mb_strtoupper($label, 'UTF-8') . '</p>';

        if ($key === 'full_name') {
            echo ' <button onclick="toggleAllNames(event)" class="ml-2 px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded-full">';
            echo '<i class="fas fa-eye" id="eye-icon-all"></i></button>';
        }

        echo ' <i class="fas fa-sort ml-2"></i>';
        echo '</th>';
        $columnIndex++;
    }

    echo '</tr>';
    echo '</thead>';

    echo '<tbody id="studentTableBody">';

    // Affichage des étudiants
    foreach ($students as $student) {
        $student_name = $DB->get_record('user', array('id' => $student->user_id));
        $student_fullname = ucwords(strtolower($student_name->firstname)) . ' ' . ucwords(strtolower($student_name->lastname));
        $assignedqcm = $DB->get_record('studentqcm_assignedqcm', array('user_id' => $student->user_id, 'sessionid' => $session->id));

        echo '<tr id="row-' . $student->user_id . '" class="border-t hover:bg-gray-50">';

        echo '<td class="px-3 py-4 text-md text-gray-600">' . $student->user_id . '</td>';
        echo '<td class="px-3 py-4 text-md text-gray-600">';
        echo '<div id="name-' . $student->user_id . '" class="text-gray-600 hidden">' . $student_fullname . '</div>';
        echo '</td>';
        echo '<td class="px-3 py-4 text-md text-gray-600">' . $assignedqcm->prod1_id . '</td>';
        echo '<td class="px-3 py-4 text-md text-gray-600">' . $assignedqcm->prod2_id . '</td>';
        echo '<td class="px-3 py-4 text-md text-gray-600">' . $assignedqcm->prod3_id . '</td>';
        echo '<input type="hidden" name="updatedData[]" id="updatedData-' . $student->user_id . '" value="">';

        // Ajout des boutons Modifier/Supprimer
        echo '<td class="p-4 text-md text-gray-600">
                <button type="button" onclick="editRow(this, ' . $student->user_id . ')" class="px-4 py-2 min-w-40 bg-indigo-400 hover:bg-indigo-500 text-white text-md font-semibold rounded-2xl">' . get_string('edit', 'mod_studentqcm') . '</button>
            </td>';

        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Bouton pour soumettre les nouvelles lignes
    echo "<div class='mb-4 mt-4 flex justify-end space-x-2 text-lg'>";
        echo '<button type="submit" id="saveButton" name="save_students" class="inline-block px-4 py-2 font-semibold rounded-2xl bg-lime-400 hover:bg-lime-500 text-white no-underline ">'
        . get_string('save_changes', 'mod_studentqcm') . '</button>';
    echo "</div>";


    echo '</form>'; // Fermeture du formulaire
    echo '</div>';
}
else {
    // Récupération des enseignants
    $teachers = $DB->get_records('pr_assigned_student_teacher', ['sessionid' => $session->id]);

    echo "<div class='flex mt-8 text-lg justify-center gap-4'>";
        // Bouton pour déclencher manuellement l'attribution automatique
        echo '<button onclick="triggerTeacherAttribution()" class="block px-4 py-2 font-semibold rounded-2xl bg-amber-400 hover:bg-amber-500 text-white no-underline">'
            . get_string('trigger_attribution', 'mod_studentqcm') . '</button>';

        // Bouton pour ajouter une nouvelle ligne
        echo '<button onclick="addTeacherRow()" class="inline-block px-4 py-2 font-semibold rounded-2xl bg-amber-400 hover:bg-amber-500 text-white no-underline">' 
            . get_string('add_attribution', 'mod_studentqcm') . '</button>';
    echo "</div>";

    echo '<div class="mt-8">';

    // Formulaire pour enregistrer les nouvelles lignes
    echo "<form id='attributionForm' method='post' action='submit_attribution_teacher.php?id={$id}'>";

    echo '<table class="min-w-full bg-white rounded-3xl shadow-md" id="teacherTable">';
    echo '<thead>';
    echo '<tr class="bg-gray-100 text-left">';

    $columns = [
        'teacher_id' => get_string('teacher_id', 'mod_studentqcm'),
        'full_name' => get_string('full_name', 'mod_studentqcm'),
        'student_id' => get_string('student_id', 'mod_studentqcm'),
        'actions' => get_string('actions', 'mod_studentqcm')
    ];

    $columnIndex = 0;
    foreach ($columns as $key => $label) {
        $roundedClass = ($columnIndex == 0) ? 'rounded-tl-3xl' : (($columnIndex == count($columns) - 1) ? 'rounded-tr-3xl' : '');

        echo '<th class="px-3 py-3 text-sm font-medium text-gray-500 uppercase tracking-wider cursor-pointer ' . $roundedClass . '"
                onclick="sortTable(' . $columnIndex . ', \'teacherTable\')">
                ' . mb_strtoupper($label, 'UTF-8');

        if ($key === 'full_name') {
            echo ' <button onclick="toggleAllNames(event)" class="ml-2 px-2 py-1 bg-gray-200 hover:bg-gray-300 rounded-full">';
            echo '<i class="fas fa-eye" id="eye-icon-all"></i></button>';
        }

        echo ' <i class="fas fa-sort ml-2"></i>';
        echo '</th>';
        $columnIndex++;
    }

    echo '</tr>';
    echo '</thead>';

    echo '<tbody id="teacherTableBody">';

    // Affichage des étudiants
    foreach ($teachers as $teacher) {
        $teacher_name = $DB->get_record('user', array('id' => $teacher->teacherid));
        $teacher_fullname = ucwords(strtolower($teacher_name->firstname)) . ' ' . ucwords(strtolower($teacher_name->lastname));

        echo '<tr id="row-' . $teacher->id . '" class="border-t hover:bg-gray-50">';

        echo '<td class="px-3 py-4 text-md text-gray-600">' . $teacher->teacherid . '</td>';
        echo '<td class="px-3 py-4 text-md text-gray-600">';
        echo '<div id="name-' . $teacher->teacherid. '" class="text-gray-600 hidden">' . $teacher_fullname . '</div>';
        echo '</td>';
        echo '<td class="px-3 py-4 text-md text-gray-600">' . $teacher->userid. '</td>';
        echo '<input type="hidden" name="updatedData[]" id="updatedData-' . $teacher->teacherid . '" value="">';

        // Ajout des boutons Modifier/Supprimer
        echo '<td class="p-4 text-md text-gray-600">
                <button type="button" onclick="editTeacherRow(this, ' . $teacher->id .', ' . $teacher->teacherid . ')" class="px-4 py-2 min-w-40 bg-indigo-400 hover:bg-indigo-500 text-white text-md font-semibold rounded-2xl">' . get_string('edit', 'mod_studentqcm') . '</button>
            </td>';

        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    // Bouton pour soumettre les nouvelles lignes
    echo "<div class='mb-4 mt-4 flex justify-end space-x-2 text-lg'>";
        echo '<button type="submit" id="saveButton" name="save_teachers" class="inline-block px-4 py-2 font-semibold rounded-2xl bg-lime-400 hover:bg-lime-500 text-white no-underline ">'
        . get_string('save_changes', 'mod_studentqcm') . '</button>';
    echo "</div>";


    echo '</form>'; // Fermeture du formulaire
    echo '</div>';
}

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

function toggleAllNames(event) {
    event.preventDefault(); 

    var nameDivs = document.querySelectorAll('[id^="name-"]');
    var eyeIcon = document.getElementById('eye-icon-all');

    nameDivs.forEach(nameDiv => nameDiv.classList.toggle('hidden'));
    eyeIcon.classList.toggle('fa-eye-slash');
    eyeIcon.classList.toggle('fa-eye');
}

let i = 0;

function addRow() {
    let tableBody = document.getElementById("studentTableBody");

    // Création d'une nouvelle ligne
    let newRow = document.createElement("tr");
    newRow.classList.add("border-t", "hover:bg-gray-50");

    // Colonnes à ajouter
    let columns = ["student_id", "full_name", "prod1", "prod2", "prod3", "actions"];

    columns.forEach((col, index) => {
        let newCell = document.createElement("td");
        newCell.classList.add("px-3", "py-4", "text-md", "text-gray-600");

        // Créer l'input pour les champs, sauf pour "actions"
        if (col !== "actions") {
            let input = document.createElement("input");
            input.type = "text";
            input.name = `${col}`;

            if (col === "full_name") {
                input.disabled = true; // Désactivation du champ "Nom complet"
                input.placeholder = ""; // Placeholder si nécessaire
                input.classList.add("border", "rounded", "px-2", "py-1", "w-full", "bg-gray-200", "text-gray-500", "cursor-not-allowed");
            } else {
                if (col !== "prod3") {
                    input.required = true; // Rendre le champ obligatoire pour les autres colonnes
                }
                input.classList.add("border", "rounded", "px-2", "py-1", "w-full");
            }

            newCell.appendChild(input);
        } else {
            // Créer la cellule "Actions" avec le bouton "Enregistrer"
            let actionsCell = document.createElement("td");
            actionsCell.classList.add("text-center"); // Centrage du bouton pour une meilleure lisibilité

            let saveButton = document.createElement("button");
            saveButton.innerText = "Enregistrer";
            saveButton.classList.add("px-4", "py-2", "bg-lime-400", "hover:bg-lime-500", "text-white", "text-md", "font-semibold", "rounded-2xl", "w-[80%]");
            saveButton.onclick = function(event) { 
                event.preventDefault(); // Empêche le rechargement immédiat du formulaire
                saveRow(saveButton);
            };

            // Ajout du bouton dans la cellule "Actions"
            actionsCell.appendChild(saveButton);
            newCell = actionsCell; // Remplace la cellule "Actions"
        }

        newRow.appendChild(newCell);
    });

    // Ajout de la nouvelle ligne au tableau
    tableBody.insertBefore(newRow, tableBody.firstChild);
    i++;
}

function addTeacherRow() {
    let tableBody = document.getElementById("teacherTableBody");

    // Création d'une nouvelle ligne
    let newRow = document.createElement("tr");
    newRow.classList.add("border-t", "hover:bg-gray-50");

    // Colonnes à ajouter
    let columns = ["teacher_id", "full_name", "student_id", "actions"];

    columns.forEach((col, index) => {
        let newCell = document.createElement("td");
        newCell.classList.add("px-3", "py-4", "text-md", "text-gray-600");

        // Créer l'input pour les champs, sauf pour "actions"
        if (col !== "actions") {
            let input = document.createElement("input");
            input.type = "text";
            input.name = `${col}`;

            if (col === "full_name") {
                input.disabled = true; // Désactivation du champ "Nom complet"
                input.placeholder = ""; // Placeholder si nécessaire
                input.classList.add("border", "rounded", "px-2", "py-1", "w-full", "bg-gray-200", "text-gray-500", "cursor-not-allowed");
            } else {
                input.required = true; // Rendre le champ obligatoire pour les autres colonnes
                input.classList.add("border", "rounded", "px-2", "py-1", "w-full");
            }

            newCell.appendChild(input);
        } else {
            // Créer la cellule "Actions" avec le bouton "Enregistrer"
            let actionsCell = document.createElement("td");
            actionsCell.classList.add("text-center"); // Centrage du bouton pour une meilleure lisibilité

            let saveButton = document.createElement("button");
            saveButton.innerText = "Enregistrer";
            saveButton.classList.add("px-4", "py-2", "bg-lime-400", "hover:bg-lime-500", "text-white", "text-md", "font-semibold", "rounded-2xl", "w-[80%]");
            saveButton.onclick = function(event) { 
                event.preventDefault(); // Empêche le rechargement immédiat du formulaire
                saveTeacherRow(saveButton);
            };

            // Ajout du bouton dans la cellule "Actions"
            actionsCell.appendChild(saveButton);
            newCell = actionsCell; // Remplace la cellule "Actions"
        }

        newRow.appendChild(newCell);
    });

    // Ajout de la nouvelle ligne au tableau
    tableBody.insertBefore(newRow, tableBody.firstChild);
    i++;
}


function saveRow(button) {
    let row = button.closest('tr');
    let inputs = row.querySelectorAll('input:not([disabled])'); // Sélectionne seulement les champs modifiables
    let allFilled = true;

    inputs.forEach(input => {
        // Exclure full_name et prod3 de la validation
        if ((input.name.indexOf('full_name') === -1 && input.name.indexOf('prod3') === -1 && input.name.indexOf('actions') === -1) && !input.value.trim()) {
            allFilled = false;
        }
    });

    if (!allFilled) {
        alert("Les champs 'Student ID', 'Production 1' et 'Production 2' doivent être remplis !");
        return;
    }

    let studentId = null;

    // Remplace les champs par leur valeur
    inputs.forEach(input => {
        if (input.name === "student_id"){
            studentId = input.value;
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
        editRow(editButton, studentId, row); // Appeler la fonction editRow pour activer la modification des champs
    };

    // Ajouter le bouton "Modifier" dans la cellule d'action
    actionsCell.appendChild(editButton);

    let form = document.getElementById('attributionForm');
    
    inputs.forEach(input => {
        let studentInput = document.createElement("input");
        studentInput.type = "hidden"; // Pas visible pour l'utilisateur
        studentInput.name = `save_students[${i}][${input.name}]`; // Prend le nom de l'input
        studentInput.value = input.value; // La valeur de l'input
        form.appendChild(studentInput);
    });

}

function saveTeacherRow(button) {
    let row = button.closest('tr');
    let inputs = row.querySelectorAll('input:not([disabled])'); // Sélectionne seulement les champs modifiables
    let allFilled = true;

    inputs.forEach(input => {
        // Exclure full_name et prod3 de la validation
        if ((input.name.indexOf('full_name') === -1 && input.name.indexOf('actions') === -1) && !input.value.trim()) {
            allFilled = false;
        }
    });

    if (!allFilled) {
        alert("Les champs 'ID de l'enseignant', 'ID de l'étudiant' doivent être remplis !");
        return;
    }

    let teacherId = null;

    // Remplace les champs par leur valeur
    inputs.forEach(input => {
        if (input.name === "teacher_id"){
            teacherId = input.value;
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
        editTeacherRow(editButton, null, teacherId, row); // Appeler la fonction editRow pour activer la modification des champs
    };

    // Ajouter le bouton "Modifier" dans la cellule d'action
    actionsCell.appendChild(editButton);

    let form = document.getElementById('attributionForm');
    
    inputs.forEach(input => {
        let teacherInput = document.createElement("input");
        teacherInput.type = "hidden"; // Pas visible pour l'utilisateur
        teacherInput.name = `save_teachers[${i}][${input.name}]`; // Prend le nom de l'input
        teacherInput.value = input.value; // La valeur de l'input
        form.appendChild(teacherInput);
    });

}


let updatedData = [];

function editRow(button, studentId, row = null) {
    if (row == null) {
        row = document.getElementById("row-" + studentId);
    }
    let cells = row.querySelectorAll("td");

    if (button.innerText === "Modifier") {
        // Activer les champs de saisie pour modifier les produits
        cells.forEach((cell, index) => {
            if (index === 2 || index === 3 || index === 4) {
                let cellContent = cell.innerHTML.trim();
                let input = document.createElement("input");
                input.type = "text";
                input.value = cellContent;
                input.name = "prod" + (index - 1);
                cell.innerHTML = '';
                cell.appendChild(input);
            }
        });

        button.innerText = "Enregistrer";
    } else {

        let studentData = { studentId: studentId };
        let allFilled = true;

        let inputs = row.querySelectorAll("input"); 
        inputs.forEach((input) => {
            let value = input.value.trim();
            
            if (input.name === "student_id") {
                studentData.studentId = value;
                if (value === "") {
                    allFilled = false;
                }
            } else if (input.name === "prod1" || input.name === "prod2") {
                studentData[input.name] = value === "" ? null : value; // Met null si vide
                if (value === "") {
                    allFilled = false;
                }
            } else if (input.name === "prod3") {
                studentData.prod3 = value === "" ? null : value; // Prod3 facultatif
            }
        });


        // Vérifier si les champs obligatoires sont remplis
        if (!allFilled) {
            alert("Les champs 'ID de l'étudiant', 'Production 1' et 'Production 2' doivent être remplis !");
            return;
        }


        if (Object.keys(studentData).length > 1) {
            let index = updatedData.findIndex(data => data.studentId === studentId);
            if (index !== -1) {
                updatedData[index] = studentData;  // Mettre à jour si trouvé
            } else {
                updatedData.push(studentData);  // Ajouter s'il n'existe pas
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


function editTeacherRow(button, id, teacherId, row = null) {
    if (row == null) {
        row = document.getElementById("row-" + id);
    }
    let cells = row.querySelectorAll("td");

    if (button.innerText === "Modifier") {
        // Activer les champs de saisie pour modifier les produits
        cells.forEach((cell, index) => {
            // En fonction de l'index des colonnes modifiables
            if (index === 0 || index === 2) { // teacher_id (index 0) et student_id (index 1)
                let cellContent = cell.innerHTML.trim();
                let input = document.createElement("input");
                input.type = "text";
                input.value = cellContent;
                input.name = (index === 0) ? "teacher_id" : "student_id"; // Nom de l'input
                cell.innerHTML = ''; // Effacer le contenu actuel de la cellule
                cell.appendChild(input); // Ajouter l'input
            }
        });

        button.innerText = "Enregistrer";
    } else {

        let teacherData = {id : id};
        if (id === null) {
            let id = (row.id).match(/^row-(\d+)$/);
            teacherData = {id : id}
        }
        let allFilled = true;

        let inputs = row.querySelectorAll("input"); 
        inputs.forEach((input) => {
            let value = input.value.trim();
            
            if (input.name === "teacher_id") {
                teacherData.teacherId = value;
                if (value === "") {
                    allFilled = false;
                }
            } else if (input.name === "student_id") {
                teacherData.studentId = value; // Met null si vide
                if (value === "") {
                    allFilled = false;
                }
            } 
        });


        // Vérifier si les champs obligatoires sont remplis
        if (!allFilled) {
            alert("Les champs 'ID de l'enseignant', 'ID de l'étudiant' doivent être remplis !");
            return;
        }


        if (Object.keys(teacherData).length > 1) {
            let index = updatedData.findIndex(data => data.teacherId === teacherId);
            if (index !== -1) {
                updatedData[index] = teacherData;  // Mettre à jour si trouvé
            } else {
                updatedData.push(teacherData);  // Ajouter s'il n'existe pas
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


// // Soumettre le formulaire manuellement
// document.getElementById('saveStudentsButton').addEventListener('click', function() {
    
//     document.querySelectorAll('input[name="updatedData[]"]').forEach(input => {
//         if (input.value.trim() === "" || input.value === "{}") {
//             input.remove();
//         }
//     });

//     // Vérifier que `updatedData` contient uniquement des entrées valides
//     updatedData = updatedData.filter(item => Object.keys(item).length > 1);

//     // Ajouter les données mises à jour au formulaire
//     updatedData.forEach(item => {
//         let input = document.createElement("input");
//         input.type = "hidden";
//         input.name = "updatedData[]";  // Si plusieurs étudiants sont envoyés
//         input.value = JSON.stringify(item);
//         document.getElementById("attributionForm").appendChild(input);
//     });

//     // Soumettre le formulaire
//     let form = document.getElementById('attributionForm');
//     if (form) {
//         form.submit();
//     } else {
//         console.error("Formulaire 'attributionForm' introuvable !");
//     }
// });

// Soumettre le formulaire manuellement
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('saveButton').addEventListener('click', function() {
        
        document.querySelectorAll('input[name="updatedData[]"]').forEach(input => {
            if (input.value.trim() === "" || input.value === "{}") {
                input.remove();
            }
        });

        // Vérifier que `updatedData` contient uniquement des entrées valides
        updatedData = updatedData.filter(item => Object.keys(item).length > 1);

        // Ajouter les données mises à jour au formulaire
        updatedData.forEach(item => {
            let input = document.createElement("input");
            input.type = "hidden";
            input.name = "updatedData[]";  // Si plusieurs étudiants sont envoyés
            input.value = JSON.stringify(item);
            document.getElementById("attributionForm").appendChild(input);
        });

        // Soumettre le formulaire
        let form = document.getElementById('attributionForm');
        if (form) {
            form.submit();
        } else {
            console.error("Formulaire 'attributionForm' introuvable !");
        }
    });
});

function triggerStudentAttribution() {
    if (!confirm("Voulez-vous vraiment déclencher l'attribution des productions entre étudiants ? Celle-ci écrasera les attributions existantes.")) {
        return;
    }

    fetch('/mod/studentqcm/trigger_student_attribution.php?sesskey=' + M.cfg.sesskey, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.text()) // Récupérer la réponse sous forme de texte
    .then(text => {
        try {
            // Tenter de parser en JSON
            let data = JSON.parse(text);
            require(['core/notification'], function(notification) {
                notification.addNotification({
                    message: data.message,
                    type: data.success ? 'success' : 'error'
                });
            });
        } catch (error) {
            // Si la réponse n'est pas un JSON valide, on affiche son contenu brut comme erreur
            require(['core/notification'], function(notification) {
                notification.addNotification({
                    message: text,
                    type: 'error'
                });
            });
        }
    })
    .catch(error => {
        console.error('Erreur lors du déclenchement de l\'attribution:', error);
        require(['core/notification'], function(notification) {
            notification.addNotification({
                message: "Une erreur est survenue. Consultez la console pour plus de détails.",
                type: 'error'
            });
        });
    });
}

function triggerTeacherAttribution() {
    if (!confirm("Voulez-vous vraiment déclencher l'attribution des productions aux enseignants ? Celle-ci écrasera les attributions existantes.")) {
        return;
    }

    fetch('/mod/studentqcm/trigger_teacher_attribution.php?sesskey=' + M.cfg.sesskey, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        credentials: 'same-origin'
    })
    .then(response => response.text()) // Récupérer la réponse sous forme de texte
    .then(text => {
        try {
            // Tenter de parser en JSON
            let data = JSON.parse(text);
            require(['core/notification'], function(notification) {
                notification.addNotification({
                    message: data.message,
                    type: data.success ? 'success' : 'error'
                });
            });
        } catch (error) {
            // Si la réponse n'est pas un JSON valide, on affiche son contenu brut comme erreur
            require(['core/notification'], function(notification) {
                notification.addNotification({
                    message: text,
                    type: 'error'
                });
            });
        }
    })
    .catch(error => {
        console.error('Erreur lors du déclenchement de l\'attribution:', error);
        require(['core/notification'], function(notification) {
            notification.addNotification({
                message: "Une erreur est survenue. Consultez la console pour plus de détails.",
                type: 'error'
            });
        });
    });
}

</script>