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
            <h3 class="text-2xl font-bold">${competence.name}</h3> <!-- Compétence -->
            
            <div id="add_subcompetences-container${index_competence}" class="mt-4">
            ${competence.subCompetences.map((sub, subIndex) => `
                <div id="subcompetence-container${index_competence}${subIndex}" class="mb-2" style="margin-left: 25px;">
                    <h4 class="text-xl font-semibold">${sub.name}</h4> <!-- Sous-compétence -->

                    <div id="keyword-container${index_competence}${subIndex}" style="margin-left: 25px;">
                    ${sub.keywords.map((keyword, keyIndex) => `
                        <p class="text-gray-700">${keyword}</p> <!-- Mot-clé -->
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
</script>
