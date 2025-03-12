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

echo "<div class='border-b p-2' style='margin-bottom: 1rem !important'>";
    echo "<div class='flex text-center text-gray-600 items-end'>";
        echo "<p class='text-3xl font-semibold'> " . get_string('info_section_general', 'mod_studentqcm') . "</p>";
    echo "</div>";
echo "</div>";

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
$competencies = $DB->get_records('competency', ['referentiel' => $session->referentiel]);
echo "<script>console.log($session->referentiel);</script>";


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

    <div class="border-b p-2" style="margin-top: 4rem !important;">
        <div class="flex text-center text-gray-600 items-end">
            <p class="text-3xl font-semibold"><?php echo get_string('info_section_competency', 'mod_studentqcm'); ?></p>
        </div>    
    </div>

    <div id="add_competences-container" class="p-4 rounded-3xl bg-gray-50"></div>

    <div class="flex justify-end mt-4">
        <button type="submit" class="inline-block px-4 py-2 font-semibold rounded-2xl bg-lime-400 hover:bg-lime-500 cursor-pointer text-white text-lg"><?php echo get_string('save', 'mod_studentqcm'); ?></button>
    </div>
</form>

<?= $OUTPUT->footer(); ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    editCompetenceField();
});

function editCompetenceField() {
    let container = document.getElementById("add_competences-container");
    container.innerHTML = "";

    // Vérifier si les compétences existent et si elles sont non vides
    if (!competencies || competencies.length === 0) {
        let noCompetenceMessage = `
            <div class="p-4 text-center text-gray-600 bg-gray-100 border border-gray-300 rounded-lg mt-4">
                <p class="text-xl font-semibold"><?php echo get_string('competency_not_found', 'mod_studentqcm'); ?></p>
            </div>
        `;
        container.insertAdjacentHTML("beforeend", noCompetenceMessage);
    } else {
        // Si des compétences existent, on les affiche comme avant
        competencies.forEach((competence, index_competence) => {
            let fieldHTML = `
            <div id="competence-container${index_competence}" class="mt-4">
                <div class="flex justify-between items-center border-b pb-1">
                    <div class="flex items-center">
                        <i class="fas fa-book text-lime-400 text-xl mr-2"></i>
                        <p class="text-2xl font-bold text-gray-600 capitalize">${competence.name}</p>
                    </div>
                    <button type="button" class="px-4 py-2 bg-red-500 text-white text-md font-semibold rounded-xl hover:bg-red-600" onclick="deleteCompetence(${index_competence})">
                        <i class="fas fa-trash-alt mr-2"></i> <?php echo get_string('delete', 'mod_studentqcm'); ?>
                    </button>
                </div>
                <div id="add_subcompetences-container${index_competence}" class="mt-2">
                    ${competence.subCompetences.map((sub, subIndex) => 
                        `
                        <div id="subcompetence-container${index_competence}${subIndex}" class="mb-2 pl-6">
                            <div class="flex justify-between items-center border-b pb-1">
                                <div class="flex items-center">
                                    <i class="fas fa-award text-sky-400 mr-2 text-xl"></i>
                                    <p class="text-xl font-semibold text-gray-600 capitalize">${sub.name}</p>
                                </div>
                                <button type="button" class="px-4 py-2 bg-red-500 text-white text-md font-semibold rounded-xl hover:bg-red-600" onclick="deleteSubCompetence(${index_competence}, ${subIndex})">
                                    <i class="fas fa-trash-alt mr-2"></i> <?php echo get_string('delete', 'mod_studentqcm'); ?>
                                </button>
                            </div>
                            <div id="keyword-container${index_competence}${subIndex}" class="mt-2 pl-6">
                                ${sub.keywords.map((keyword) => 
                                    `
                                    <div class="flex items-center text-gray-600 mt-2">
                                        <i class="fas fa-tag text-gray-400 mr-2 text-md"></i>
                                        <p class="text-md capitalize">${keyword}</p>
                                    </div>
                                    `
                                ).join('')}
                            </div>
                        </div>
                        `
                    ).join('')}
                </div>
            </div>
            `;
            container.insertAdjacentHTML("beforeend", fieldHTML);
        });
    }

    let addButtonHTML = `
        <div class="flex justify-end mt-4">
            <button type="button" class="px-4 py-2 bg-indigo-400 text-white text-md font-semibold rounded-xl hover:bg-indigo-500" onclick="addCompetenceField()"><i class="fas fa-plus mr-2"></i> <?php echo get_string('add_competency', 'mod_studentqcm'); ?></button>
        </div>
    `;
    container.insertAdjacentHTML("beforeend", addButtonHTML);
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
        <div id="competence-container${index_competence}" class="p-4 rounded-2xl bg-white mt-4">
            <p class="text-2xl text-gray-600 font-bold capitalize"> <?php echo get_string('newcompetency', 'mod_studentqcm'); ?></p>
            
            <div class="flex items-center space-x-4 mt-4">
                <input type="text" id="competence-name${index_competence}" placeholder="<?php echo get_string('name_competence', 'mod_studentqcm'); ?>" class="p-2 border rounded-xl w-full focus:outline-none focus:ring-2 focus:ring-indigo-200" required>

                <button type="button" class="px-4 py-2 bg-indigo-400 text-white text-md font-semibold rounded-xl hover:bg-indigo-500 whitespace-nowrap capitalize" onclick="addSubCompetenceField(${index_competence})">
                    <?php echo get_string('add_subcompetences', 'mod_studentqcm'); ?>
                </button>
            </div>

            <div id="add_subcompetences-container${index_competence}" class="mt-2"></div>

            <div class="flex mt-4 justify-end space-x-2">
                <button type="button" class="px-4 py-2 bg-lime-400 text-white text-md font-semibold rounded-xl hover:bg-lime-500 whitespace-nowrap capitalize" onclick="saveCompetence(${index_competence})">
                    <?php echo get_string('save', 'mod_studentqcm'); ?>
                </button>
                <button type="button" class="px-4 py-2 bg-gray-400 text-white text-md font-semibold rounded-xl hover:bg-gray-500 whitespace-nowrap capitalize" onclick="deleteAddCompetence(${index_competence})">
                    <?php echo get_string('cancel', 'mod_studentqcm'); ?>
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
        <div id="subcompetence-container${index_competence}${index_sub}" class="ml-6">
            <div class="flex items-center space-x-4">
                <input type="text" id="subcompetence-name${index_competence}${index_sub}" placeholder="<?php echo get_string('name_subcompetence', 'mod_studentqcm'); ?>" class="p-2 border rounded-xl w-full focus:outline-none focus:ring-2 focus:ring-indigo-200" required>

                <button type="button" class="px-4 py-2 bg-indigo-400 text-white text-md font-semibold rounded-xl hover:bg-indigo-500 whitespace-nowrap" onclick="addKeywordField(${index_competence}, ${index_sub})">
                    <?php echo get_string('add_keyword', 'mod_studentqcm'); ?>
                </button>
            </div>
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
        <input type="text" id="keyword${index_competence}${index_sub}${index_keyword}" class="mt-2 p-2 border rounded-xl w-full focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="<?php echo get_string('name_keyword', 'mod_studentqcm'); ?>" required>
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
