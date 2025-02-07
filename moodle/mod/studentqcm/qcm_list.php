<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$userid = $USER->id;

// Récupérer toutes les questions créées par l'utilisateur
$qcms = $DB->get_records('studentqcm_question', array('userid' => $userid), 'id DESC');

// Charger les noms des référentiels, compétences, sous-compétences et mots-clés
$referentiels = $DB->get_records_menu('referentiel', null, '', 'id, name');
$competencies = $DB->get_records_menu('competency', null, '', 'id, name');
$subcompetencies = $DB->get_records_menu('subcompetency', null, '', 'id, name');

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/qcm_list.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
    echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('qcm_list', 'mod_studentqcm') . "</p>";
echo "</div>";

// Boutons de navigation
echo "<div class='flex mt-8 text-lg justify-between'>";
    echo "<a href='view.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
    echo "<i class='fas fa-arrow-left mr-2'></i>";
    echo get_string('back', 'mod_studentqcm');
    echo "</a>";

    echo "<a href='qcm_add.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-lime-300 hover:bg-lime-400 cursor-pointer text-lime-700 no-underline'>";
    echo "<i class='fas fa-plus mr-2'></i>";
    echo get_string('add_qcm', 'mod_studentqcm');
    echo "</a>";
echo "</div>";

if ($qcms) {
    echo "<div class='space-y-4 mt-4'>";
    
    foreach ($qcms as $qcm) {
        $nom_referentiel = isset($referentiels[$qcm->referentiel]) ? $referentiels[$qcm->referentiel] : get_string('unknown', 'mod_studentqcm');
        $nom_competency = isset($competencies[$qcm->competency]) ? $competencies[$qcm->competency] : get_string('unknown', 'mod_studentqcm');
        $nom_subcompetency = isset($subcompetencies[$qcm->subcompetency]) ? $subcompetencies[$qcm->subcompetency] : get_string('unknown', 'mod_studentqcm');

        echo "<div class='p-4 bg-white rounded-3xl shadow flex items-center justify-between'>";

            // Partie gauche (question + infos)
            echo "<div>";
            echo "<p class='font-semibold text-2xl text-gray-700 flex items-center gap-2 mb-4'>";
            echo format_string(ucfirst($qcm->question));
            echo "</p>";

            // Informations sur le référentiel, compétence et sous-compétence
            echo "<div class='mt-2 text-gray-600 text-sm flex flex-col space-y-1'>";

            // Référentiel
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-book text-green-500'></i>";
            echo "<span>" . get_string('referentiel', 'mod_studentqcm') . ": <strong>" . ucfirst($nom_referentiel) . "</strong></span>";
            echo "</p>";

            // Compétence
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-bookmark text-orange-500'></i>";
            echo "<span>" . get_string('competency', 'mod_studentqcm') . ": <strong>" . ucfirst($nom_competency) . "</strong></span>";
            echo "</p>";

            // Sous-compétence
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-award text-purple-500'></i>";
            echo "<span>" . get_string('subcompetency', 'mod_studentqcm') . ": <strong>" . ucfirst($nom_subcompetency) . "</strong></span>";
            echo "</p>";

            echo "</div>";
            echo "</div>";


            // Partie droite (boutons)
            echo "<div class='flex space-x-2'>";
                echo "<a href='qcm_edit.php?id={$id}&qcm_id={$qcm->id}' class='px-3 py-2 bg-sky-400 text-white rounded-lg hover:bg-sky-500'>";
                echo "<i class='fas fa-edit'></i>";
                echo "</a>";

                echo "<a href='#' class='px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600' onclick='showDeleteModal({$qcm->id}); return false;'>";
                echo "<i class='fas fa-trash'></i>";
                echo "</a>";
            echo "</div>";


        echo "</div>";
    }
    
    echo "</div>";
} else {
    echo "<p class='text-center text-lg text-gray-600'>" . get_string('qcm_not_found', 'mod_studentqcm') . "</p>";
}

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

    let qcmToDeleteId = null;

    function showDeleteModal(qcmId) {
        qcmToDeleteId = qcmId;
        deleteModal.classList.remove('hidden');
    }

    closeModalBtn.addEventListener('click', function () {
        deleteModal.classList.add('hidden');
    });

    confirmDeleteBtn.addEventListener('click', function () {
        if (qcmToDeleteId) {
            window.location.href = `qcm_delete.php?id=${<?php echo $id; ?>}&qcm_id=${qcmToDeleteId}`;
        }
    });
</script>

