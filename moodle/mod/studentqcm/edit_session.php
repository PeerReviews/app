<?php
require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);
$session_id = required_param('session_id', PARAM_INT);
$session = $DB->get_record('studentqcm', ['id' => $session_id], '*', MUST_EXIST);
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);

require_login();
$context = context_module::instance($cm->id);
$PAGE->set_context($context);
$PAGE->set_url('/mod/studentqcm/edit_session.php', ['id' => $id]);
$PAGE->set_title(format_string($session->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', ['v' => time()]));

echo $OUTPUT->header();

// Début du formulaire
echo '<form action="save_session.php?id=' . $id . '&session_id=' . $session->id . '" method="post" class="session-form">';
echo '<input type="hidden" name="session_id" value="' . $session->id . '">';

$fields = [
    'name' => 'Nom de la session',
    'intro' => 'Introduction',
    'referentiel' => 'Référentiel',
    'nbreviewers' => 'Nombre de relecteurs',
    'nbqcm' => 'Nombre de QCM',
    'nbqcu' => 'Nombre de QCU',
    'nbtcs' => 'Nombre de TCS',
    'nbpop' => 'Nombre de Pop',
];

foreach ($fields as $field => $label) {
    echo '<div class="form-group">';
    echo '<label for="' . $field . '">' . $label . ' : </label>';
    
    // Vérifier si le champ est "referentiel"
    if ($field == 'referentiel') {
        // Récupérer le nom du référentiel à partir de son ID
        $referentiel_name = $DB->get_field('referentiel', 'name', ['id' => $session->$field]);
        echo '<input type="text" id="' . $field . '" name="' . $field . '" value="' . htmlspecialchars($referentiel_name) . '" required>';
    } else {
        // Pour les autres champs, déterminer le type d'entrée
        $type = in_array($field, ['nbqcm', 'nbqcu', 'nbtcs', 'nbpop', 'nbreviewers']) ? 'number' : 'text';
        echo '<input type="' . $type . '" id="' . $field . '" name="' . $field . '" value="' . htmlspecialchars($session->$field) . '" required>';
    }

    echo '</div>';
}

// Champs de date
$date_fields = ['start_date_session', 'end_date_session', 'date_jury', 'start_date_1', 'end_date_1', 'end_date_tt_1',
                'start_date_2', 'end_date_2', 'end_date_tt_2',
                'start_date_3', 'end_date_3', 'end_date_tt_3'];

foreach ($date_fields as $field) {
    echo '<div class="form-group">';
    echo '<label for="' . $field . '">' . ucfirst(str_replace('_', ' ', $field)) . ' : </label>';
    echo '<input type="datetime-local" id="' . $field . '" name="' . $field . '" value="' . 
         (!empty($session->$field) ? date('Y-m-d\TH:i', $session->$field) : '') . '">';
    echo '</div>';
}

// Récupération des données de compétences
$competencies = $DB->get_records('competency', ['referentiel' => $session->referentiel]);

$competency_data = [];
foreach ($competencies as $competency) {
    $subcompetencies = $DB->get_records('subcompetency', ['competency' => $competency->id]);

    $subcompetency_data = [];
    foreach ($subcompetencies as $subcompetency) {
        $keywords = $DB->get_records('keyword', ['subcompetency' => $subcompetency->id]);

        $subcompetency_data[] = [
            'id' => $subcompetency->id,
            'name' => $subcompetency->name,
            'keywords' => array_values(array_map(fn($kw) => $kw->word, $keywords)),
        ];
    }

    $competency_data[] = [
        'id' => $competency->id,
        'name' => $competency->name,
        'subCompetences' => $subcompetency_data,
    ];
}

// Convertir en JSON pour le script JS
$competency_json = json_encode($competency_data);
echo "<script>let competencies = $competency_json;</script>";

?>

<form action="save_session.php?id=<?= $id ?>&session_id=<?= $session->id ?>" method="post" class="session-form">
    <input type="hidden" name="session_id" value="<?= $session->id ?>">

    <div id="add_competences-container"></div>

    <button type="submit" class="btn-save">Sauvegarder</button>
</form>

<form id="competence-form" class="session-form">
    <input type="hidden" name="session_id" value="<?= $session->id ?>">

    <div id="add_competences-container"></div>

    <button type="button" onclick="addCompetenceField()" class="btn-add">Ajouter une compétence</button>
    <button type="button" onclick="saveCompetence()" class="btn-save">Sauvegarder</button>
</form>

<?= $OUTPUT->footer(); ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    editCompetenceField();
});

function editCompetenceField() {
    let container = document.getElementById("add_competences-container");

    if (!competencies || competencies.length === 0) {
        console.warn("Aucune compétence enregistrée.");
        return;
    }

    competencies.forEach((competence, index_competence) => {
    let fieldHTML = `
        <div id="competence-container${index_competence}" class="competence-block p-4 border border-gray-300 rounded-lg bg-white mt-4">
            <div class="flex justify-between items-center">
                <h3 class="text-2xl font-bold">${competence.name}</h3>
                <button type="button" class="bg-red-500 text-white px-3 py-1 rounded" onclick="deleteCompetence(${index_competence})">
                    Supprimer
                </button>
            </div>
            
            <div id="add_subcompetences-container${index_competence}" class="mt-4">
            ${competence.subCompetences.map((sub, subIndex) => `
                <div id="subcompetence-container${index_competence}${subIndex}" class="mb-2" style="margin-left: 25px;">
                    <div class="flex justify-between items-center">
                        <h4 class="text-xl font-semibold">${sub.name}</h4>
                        <button type="button" class="bg-red-500 text-white px-2 py-1 rounded" onclick="deleteSubCompetence(${index_competence}, ${subIndex})">
                            Supprimer
                        </button>
                    </div>

                    <div id="keyword-container${index_competence}${subIndex}" style="margin-left: 25px;">
                    ${sub.keywords.map((keyword, keyIndex) => `
                        <p class="text-gray-700">${keyword}</p>
                    `).join('')}
                    </div>
                </div>
            `).join('')}
            </div>
        </div>
    `;

    container.insertAdjacentHTML("beforeend", fieldHTML);
});
}

function deleteCompetence(index_competence) {
    let competenceId = competencies[index_competence].id;

    fetch('delete_competence.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ competenceId: competenceId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let competenceElement = document.getElementById(`competence-container${index_competence}`);
            if (competenceElement) {
                competenceElement.remove(); 
                competencies.splice(index_competence, 1); 
            }
        } else {
            console.error("Erreur lors de la suppression :", data.error);
        }
    })
    .catch(error => console.error('Erreur réseau:', error));
}

function deleteSubCompetence(index_competence, subIndex) {
    let subCompetenceId = competencies[index_competence].subCompetences[subIndex].id;

    fetch('delete_subcompetence.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ subCompetenceId: subCompetenceId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let subCompetenceElement = document.getElementById(`subcompetence-container${index_competence}${subIndex}`);
            if (subCompetenceElement) {
                subCompetenceElement.remove(); 
                competencies[index_competence].subCompetences.splice(subIndex, 1);
            }
        } else {
            console.error("Erreur lors de la suppression :", data.error);
        }
    })
    .catch(error => console.error('Erreur réseau:', error));
}

function addCompetenceField() {
    let index_competence = competencies.length; 
    let container = document.getElementById("add_competences-container");

    let fieldHTML = `
        <div id="competence-container${index_competence}" class="competence-block p-4 border border-gray-300 rounded-lg bg-white mt-4">
            <h3 class="text-2xl font-bold">Nouvelle compétence</h3>
            
            <label>Nom de la compétence</label>
            <input type="text" id="competence-name${index_competence}" class="form-control p-2 border rounded w-full" required>

            <button type="button" class="bg-sky-100 p-2 m-4 rounded font-bold hover:bg-gray-500" onclick="addSubCompetenceField(${index_competence})">
                Ajouter une sous-compétence
            </button>

            <div id="add_subcompetences-container${index_competence}" class="mt-4"></div>

            <div class="flex mt-4">
                <button type="button" class="bg-green-500 text-white font-bold py-2 px-4 rounded " onclick="saveCompetence(${index_competence})">
                    Enregistrer
                </button>
                <button type="button" class="bg-gray-500 text-white font-bold py-2 px-4 rounded mx-4" onclick="deleteAddCompetence(${index_competence})">
                    Annuler
                </button>
            </div>
        </div>
    `;

    container.insertAdjacentHTML("beforeend", fieldHTML);
    competencies.push({ id: null, name: "", subCompetences: [] });
}

function deleteAddCompetence(index_competence) {
    let competenceContainer = document.getElementById(`competence-container${index_competence}`);

    if (competenceContainer) {
        competenceContainer.remove(); 
    }
}


function addSubCompetenceField(index_competence) {
    let index_sub = competencies[index_competence].subCompetences.length;
    let container = document.getElementById(`add_subcompetences-container${index_competence}`);

    let fieldHTML = `
        <div id="subcompetence-container${index_competence}${index_sub}" class="ml-6 mb-2">
            <input type="text" id="subcompetence-name${index_competence}${index_sub}" class="form-control p-2 border rounded w-full" placeholder="Nom de la sous-compétence" required>
            <button type="button" class="bg-purple-300 p-2 m-2 rounded font-bold hover:bg-purple-500" onclick="addKeywordField(${index_competence}, ${index_sub})">
                Ajouter un mot-clé
            </button>
            <div id="keyword-container${index_competence}${index_sub}" class="ml-6"></div>
        </div>
    `;

    container.insertAdjacentHTML("beforeend", fieldHTML);
    competencies[index_competence].subCompetences.push({ id: null, name: "", keywords: [] });
}

function addKeywordField(index_competence, index_sub) {
    let index_keyword = competencies[index_competence].subCompetences[index_sub].keywords.length;
    let container = document.getElementById(`keyword-container${index_competence}${index_sub}`);

    let fieldHTML = `
        <input type="text" id="keyword${index_competence}${index_sub}${index_keyword}" class="form-control p-2 border rounded w-full mt-2" placeholder="Mot-clé" required>
    `;

    container.insertAdjacentHTML("beforeend", fieldHTML);
    competencies[index_competence].subCompetences[index_sub].keywords.push("");
}

function saveCompetence(index_competence) {
    let competenceName = document.getElementById(`competence-name${index_competence}`).value.trim();
    if (!competenceName) {
        alert("Veuillez entrer un nom pour la compétence.");
        return;
    }

    let subCompetences = [];
    let subContainers = document.querySelectorAll(`#add_subcompetences-container${index_competence} > div`);
    
    subContainers.forEach((subContainer, subIndex) => {
        let subName = document.getElementById(`subcompetence-name${index_competence}${subIndex}`).value.trim();
        let keywords = [];

        let keywordInputs = document.querySelectorAll(`#keyword-container${index_competence}${subIndex} input`);
        keywordInputs.forEach(input => {
            let keywordValue = input.value.trim();
            if (keywordValue) {
                keywords.push(keywordValue);
            }
        });

        subCompetences.push({ name: subName, keywords: keywords });
    });

    let competenceData = {
        name: competenceName,
        subCompetences: subCompetences
    };

    let confirmationMessage = `Voulez-vous vraiment ajouter cette compétence ?`;

    if (!confirm(confirmationMessage)) {
        return; 
    }

    // let formattedCompetenceData = JSON.stringify(competenceData).replace(/"/g, "'");
    let formattedCompetenceData = JSON.stringify(competenceData).replace(/\\/g, '');

    fetch('add_competence.php?', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(competenceData) // Envoie les données formatées
    })
    .then(response => {
        return response.text().then(text => {
            try {
                console.log(text);
                const data = JSON.parse(text); 
                return data; 
            } catch (e) {
                throw new Error('Réponse invalide du serveur, attendue JSON.');
            }
        });
    })
    .then(data => {
        if (data.success) {
            alert("Compétence ajoutée avec succès !");
            location.reload();  
        } else {
            console.error("Erreur :", data.error); 
        }
    })
    .catch(error => {
        console.error('Erreur réseau:', error.message); 
    });
 }


</script>
