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
$PAGE->set_url('/mod/studentqcm/edit_session.php', ['id' => $id, 'session_id' => $session_id]);
$PAGE->set_title(format_string($session->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', ['v' => time()]));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('edit_session', 'mod_studentqcm') . " " . format_string($session->name) . "</p>";
echo "</div>";

// Bouton retour
echo "<div class='flex mt-8 text-lg justify-between'>";
echo "<a href='admin_sessions.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 text-gray-500 no-underline'>";
echo "<i class='fas fa-arrow-left mr-2'></i>";
echo get_string('back', 'mod_studentqcm');
echo "</a>";
echo "</div>"; 

echo '<div class="mt-8 p-4 bg-white rounded-3xl shadow-lg">';

echo '<form action="save_session.php?id=' . $id . '&session_id=' . $session->id . '" method="post" class="space-y-6">';
echo '<input type="hidden" name="session_id" value="' . $session->id . '">';


$fields = [
    'name' => 'Nom de la session',
    'intro' => 'Introduction',
    'referentiel' => 'Référentiel',
    'nbreviewers' => 'Nombre de relecteurs',
    'nbqcm' => get_string('nb_qcm', 'mod_studentqcm'),
    'nbqcu' => get_string('nb_qcu', 'mod_studentqcm'),
    'nbtcs' => get_string('nb_tcs', 'mod_studentqcm'),
    'nbpop' => get_string('nb_pop', 'mod_studentqcm'),
];

echo '<div class="grid grid-cols-1 gap-6 mt-6">';

    foreach ($fields as $field => $label) {
        if (!in_array($field, ['nbqcm', 'nbqcu', 'nbtcs', 'nbpop'])) {
            echo '<div class="flex flex-col">';
            echo '<label class="font-semibold mb-2 text-lg text-gray-600">' . $label . ':</label>';
            $type = in_array($field, ['nbqcm', 'nbqcu', 'nbtcs', 'nbpop', 'nbreviewers']) ? 'number' : 'text';
            $required = $field !== 'intro' ? 'required' : '';
            if ($field == 'referentiel') {
                $referentiel_name = $DB->get_field('referentiel', 'name', ['id' => $session->$field]);
                echo '<input class="p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-300" type="' . $type . '" name="' . $field . '" value="' . htmlspecialchars($referentiel_name) . '" ' . $required . ' />';
            } 
            else {
                echo '<input class="p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-300" type="' . $type . '" name="' . $field . '" value="' . htmlspecialchars($session->$field) . '" ' . $required . ' />';
            }
            echo '</div>';
        }
    }

    echo "<div class='border-b p-2' style='margin-top: 4rem !important; margin-bottom: 1rem !important'>";
        echo "<div class='flex text-center text-gray-600 items-end'>";
            echo "<p class='text-3xl font-semibold'> " . get_string('info_section_question', 'mod_studentqcm') . "</p>";
        echo "</div>";
    echo "</div>";

    echo '<div class="grid grid-cols-4 gap-4 px-2">';
    foreach ($fields as $field => $label) {
        if (in_array($field, ['nbqcm', 'nbqcu', 'nbtcs', 'nbpop'])) {
            echo '<div class="flex flex-col">';
            echo '<label class="font-semibold mb-2 text-lg text-gray-600">' . $label . ':</label>';
            $type = in_array($field, ['nbqcm', 'nbqcu', 'nbtcs', 'nbpop', 'nbreviewers']) ? 'number' : 'text';
            $required = $field !== 'intro' ? 'required' : '';
            echo '<input class="p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-lime-300" type="' . $type . '" name="' . $field . '" value="' . htmlspecialchars($session->$field) . '" ' . $required . ' />';
            echo '</div>';
        }
    }
    echo '</div>';

echo '</div>';


echo "<div class='border-b p-2' style='margin-top: 4rem !important;'>";
    echo "<div class='flex text-center text-gray-600 items-end'>";
        echo "<p class='text-3xl font-semibold'> " . get_string('info_section_date', 'mod_studentqcm') . "</p>";
    echo "</div>";
echo "</div>";


$date_fields = [
    ['start_date_1', 'end_date_1', 'end_date_tt_1'],
    ['start_date_2', 'end_date_2', 'end_date_tt_2'],
    ['start_date_3', 'end_date_3', 'end_date_tt_3']
];

$bg_colors = ['bg-lime-200', 'bg-indigo-200', 'bg-sky-200'];
$text_colors = ['text-lime-600', 'text-indigo-600', 'text-sky-600'];
$focus_colors = ['focus:ring-lime-400', 'focus:ring-indigo-400', 'focus:ring-sky-400'];

$special_date_fields = ['start_date_session', 'end_date_session', 'date_jury'];

echo '<div class="grid grid-cols-3 gap-4 mt-4 rounded-3xl bg-indigo-50 p-4">';
foreach ($special_date_fields as $index => $field) {
    echo '<div class="flex flex-col">';
    echo '<label class="font-semibold mb-2 text-gray-600 text-lg">' . get_string($field, 'mod_studentqcm') . ' : </label>';
    echo '<input class="p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-200" type="datetime-local" id="' . $field . '" name="' . $field . '" value="' . (!empty($session->$field) ? date('Y-m-d\TH:i', $session->$field) : '') . '" />';
    echo '</div>';
}
echo '</div>';

echo '<div class="grid grid-cols-3 gap-4 mt-4">';
foreach ($date_fields as $index => $field_group) {
    $bg_color_class = $bg_colors[$index];
    $text_color_class = $text_colors[$index];
    $focus_color_class = $focus_colors[$index];

    echo '<div class="space-y-4 ' . $bg_color_class . ' p-4 rounded-3xl">';
    foreach ($field_group as $field) {
        echo '<div class="flex flex-col">';
        echo '<label class="font-semibold mb-2 ' . $text_color_class . ' text-lg">' . get_string($field, 'mod_studentqcm') . ' : </label>';
        echo '<input class="p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 ' . $focus_color_class . '" type="datetime-local" id="' . $field . '" name="' . $field . '" value="' . (!empty($session->$field) ? date('Y-m-d\TH:i', $session->$field) : '') . '" />';
        echo '</div>';
    }
    echo '</div>';
}
echo '</div>';



// Récupération des données de compétences
$competencies = $DB->get_records('competency', ['referentiel' => $session->referentiel, 'sessionid' => $session->id]);

$competency_data = [];
foreach ($competencies as $competency) {
    $subcompetencies = $DB->get_records('subcompetency', ['competency' => $competency->id, 'sessionid' => $session->id]);

    $subcompetency_data = [];
    foreach ($subcompetencies as $subcompetency) {
        $keywords = $DB->get_records('keyword', ['subcompetency' => $subcompetency->id, 'sessionid' => $session->id]);

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
        referentiel : <?php echo json_encode($session->referentiel); ?>,
        name: competenceName,
        subCompetences: subCompetences
    };

    let confirmationMessage = `Voulez-vous vraiment ajouter cette compétence ?`;

    if (!confirm(confirmationMessage)) {
        return; 
    }

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
