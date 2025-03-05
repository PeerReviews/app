<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

// Récupération du module, cours et QCM
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/manual_attribution.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css'));

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

// Récupération des étudiants
$students = $DB->get_records('studentqcm_assignedqcm');

echo '<div class="mt-8">';

// Bouton pour ajouter une nouvelle ligne
echo '<button onclick="addRow()" class="mb-2 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">' 
    . get_string('add_attribution', 'mod_studentqcm') . '</button>';

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
              onclick="sortTable(' . $columnIndex . ')">
              ' . mb_strtoupper($label, 'UTF-8');

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

echo '<tbody id="studentTableBody">';

// Affichage des étudiants
foreach ($students as $student) {
    $student_name = $DB->get_record('user', array('id' => $student->user_id));
    $student_fullname = ucwords(strtolower($student_name->firstname)) . ' ' . ucwords(strtolower($student_name->lastname));
    $assignedqcm = $DB->get_record('studentqcm_assignedqcm', array('user_id' => $student->user_id));

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
    echo '<td class="px-3 py-4 text-md text-gray-600">
            <button type="button" onclick="editRow(this, ' . $student->user_id . ')" class="px-3 py-1 bg-blue-500 text-white rounded ml-2">Modifier</button>
          </td>';

    echo '</tr>';
}

echo '</tbody>';
echo '</table>';

// Bouton pour soumettre les nouvelles lignes
echo '<button type="submit" id="saveStudentsButton" name="save_students" class="mt-4 px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">'
    . get_string('save_students', 'mod_studentqcm') . '</button>';

echo '</form>'; // Fermeture du formulaire
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
            saveButton.classList.add("px-3", "py-1", "bg-green-500", "text-white", "rounded");
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

// Soumettre le formulaire manuellement
document.getElementById('saveStudentsButton').addEventListener('click', function() {
    
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

</script>
