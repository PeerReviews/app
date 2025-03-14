<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

$cm = get_coursemodule_from_id('peerreview', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$peerreview = $DB->get_record('peerreview', array('id' => $cm->instance), '*', MUST_EXIST);
$session = $DB->get_record('peerreview', ['archived' => 0], '*', MUST_EXIST);

$required_pops = $DB->get_records('pr_question_pop', array('sessionid' => $session->id));

$userid = $USER->id;

// Récupérer toutes les questions créées par l'utilisateur
$questions = $DB->get_records('pr_question', array('userid' => $userid, 'sessionid' => $session->id), 'id DESC');

$qcms = array_filter($questions, fn($q) => $q->type === 'QCM' && !$q->ispop);
$qcus = array_filter($questions, fn($q) => $q->type === 'QCU' && !$q->ispop);
$tcss = array_filter($questions, fn($q) => $q->type === 'TCS' && !$q->ispop);
$pops = array_filter($questions, fn($q) => $q->ispop);
// Affichage de la variable $question


// Charger les noms des référentiels, compétences, sous-compétences et mots-clés
$referentiels = $DB->get_records_menu('pr_referentiel', ['sessionid' => $session->id], '', 'id, name');
$competencies = $DB->get_records_menu('pr_competency', ['sessionid' => $session->id], '', 'id, name');
$subcompetencies = $DB->get_records_menu('pr_subcompetency', ['sessionid' => $session->id], '', 'id, name');

require_login($course, true, $cm);

$PAGE->set_url('/mod/peerreview/qcm_list.php', array('id' => $id));
$PAGE->set_title(format_string($peerreview->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/peerreview/style.css', array('v' => time())));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
    echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('qcm_list', 'mod_peerreview') . "</p>";
echo "</div>";

// Boutons de navigation
echo "<div class='flex mt-8 text-lg justify-between'>";
    echo "<a href='view.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
    echo "<i class='fas fa-arrow-left mr-2'></i>";
    echo get_string('back', 'mod_peerreview');
    echo "</a>";
echo "</div>";


// Affichage des qcm
echo "<div class='flex mt-8 mx-4 justify-between border-b p-2'>";
    echo "<div class='flex text-center text-gray-500 items-end'>";
        echo "<p class='mr-4 text-4xl font-semibold'> " . count($qcms) . "/" . $peerreview->nbqcm . "</p>";
        echo "<p class='text-3xl'> " . get_string('completed_qcms', 'mod_peerreview') . "</p>";
    echo "</div>";

    if (count($qcms) < $session->nbqcm){
        echo "<a href='qcm_add.php?id={$id}&qcm_type=QCM' class='inline-block px-4 py-2 text-lg font-semibold rounded-2xl bg-lime-300 hover:bg-lime-400 cursor-pointer text-lime-700 no-underline min-w-52'>";
            echo "<i class='fas fa-plus mr-2'></i>";
            echo get_string('add_qcm', 'mod_peerreview');
        echo "</a>";
    }
   
echo "</div>";

if ($qcms) {
    
    echo "<div class='space-y-4 mt-4'>";

    foreach ($qcms as $qcm) {
        $nom_referentiel = isset($referentiels[$qcm->referentiel]) ? $referentiels[$qcm->referentiel] : get_string('unknown', 'mod_peerreview');
        $nom_competency = isset($competencies[$qcm->competency]) ? $competencies[$qcm->competency] : get_string('unknown', 'mod_peerreview');
        $nom_subcompetency = isset($subcompetencies[$qcm->subcompetency]) ? $subcompetencies[$qcm->subcompetency] : get_string('unknown', 'mod_peerreview');

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
            echo "<span>" . get_string('referentiel', 'mod_peerreview') . " : <strong>" . ucfirst($nom_referentiel) . "</strong></span>";
            echo "</p>";

            // Compétence
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-bookmark text-orange-500'></i>";
            echo "<span>" . get_string('competency', 'mod_peerreview') . " : <strong>" . ucfirst($nom_competency) . "</strong></span>";
            echo "</p>";

            // Sous-compétence
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-award text-purple-500'></i>";
            echo "<span>" . get_string('subcompetency', 'mod_peerreview') . " : <strong>" . ucfirst($nom_subcompetency) . "</strong></span>";
            echo "</p>";

            echo "</div>";
            echo "</div>";

            // Partie droite (boutons)
            if ($qcm->status == 0){
                echo "<div class='flex space-x-2'>";
                    echo "<a href='qcm_edit.php?id={$id}&qcm_id={$qcm->id}' class='px-3 py-2 bg-sky-400 text-white rounded-lg hover:bg-sky-500'>";
                    echo "<i class='fas fa-edit'></i>";
                    echo "</a>";

                    echo "<a href='#' class='px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600' onclick='showDeleteModal({$qcm->id}); return false;'>";
                    echo "<i class='fas fa-trash'></i>";
                    echo "</a>";
                echo "</div>";
            }
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p class='text-center text-lg text-gray-600 mt-4'>" . get_string('qcm_not_found', 'mod_peerreview') . "</p>";
}

// Affichage des qcu
echo "<div class='flex mt-8 mx-4 justify-between border-b p-2'>";
    echo "<div class='flex text-center text-gray-500 items-end'>";
        echo "<p class='mr-4 text-4xl font-semibold'> " . count($qcus) . "/" . $peerreview->nbqcu . "</p>";
        echo "<p class='text-3xl'> " . get_string('completed_qcus', 'mod_peerreview') . "</p>";
    echo "</div>";
    if (count($qcus) < $session->nbqcu){
        echo "<a href='qcm_add.php?id={$id}&qcm_type=QCU' class='inline-block px-4 py-2 text-lg font-semibold rounded-2xl bg-lime-300 hover:bg-lime-400 cursor-pointer text-lime-700 no-underline min-w-52'>";
            echo "<i class='fas fa-plus mr-2'></i>";
            echo get_string('add_qcu', 'mod_peerreview');
        echo "</a>";
    }
echo "</div>";

if ($qcus) {
    
    echo "<div class='space-y-4 mt-4'>";

    foreach ($qcus as $qcu) {
        $nom_referentiel = isset($referentiels[$qcu->referentiel]) ? $referentiels[$qcu->referentiel] : get_string('unknown', 'mod_peerreview');
        $nom_competency = isset($competencies[$qcu->competency]) ? $competencies[$qcu->competency] : get_string('unknown', 'mod_peerreview');
        $nom_subcompetency = isset($subcompetencies[$qcu->subcompetency]) ? $subcompetencies[$qcu->subcompetency] : get_string('unknown', 'mod_peerreview');

        echo "<div class='p-4 bg-white rounded-3xl shadow flex items-center justify-between'>";

            // Partie gauche (question + infos)
            echo "<div>";
            echo "<p class='font-semibold text-2xl text-gray-700 flex items-center gap-2 mb-4'>";
            echo format_string(ucfirst($qcu->question));
            echo "</p>";

            // Informations sur le référentiel, compétence et sous-compétence
            echo "<div class='mt-2 text-gray-600 text-sm flex flex-col space-y-1'>";

            // Référentiel
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-book text-green-500'></i>";
            echo "<span>" . get_string('referentiel', 'mod_peerreview') . " : <strong>" . ucfirst($nom_referentiel) . "</strong></span>";
            echo "</p>";

            // Compétence
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-bookmark text-orange-500'></i>";
            echo "<span>" . get_string('competency', 'mod_peerreview') . " : <strong>" . ucfirst($nom_competency) . "</strong></span>";
            echo "</p>";

            // Sous-compétence
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-award text-purple-500'></i>";
            echo "<span>" . get_string('subcompetency', 'mod_peerreview') . " : <strong>" . ucfirst($nom_subcompetency) . "</strong></span>";
            echo "</p>";

            echo "</div>";
            echo "</div>";

            // Partie droite (boutons)
            if ($qcu->status == 0){
                echo "<div class='flex space-x-2'>";
                    echo "<a href='qcm_edit.php?id={$id}&qcm_id={$qcu->id}' class='px-3 py-2 bg-sky-400 text-white rounded-lg hover:bg-sky-500'>";
                    echo "<i class='fas fa-edit'></i>";
                    echo "</a>";

                    echo "<a href='#' class='px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600' onclick='showDeleteModal({$qcu->id}); return false;'>";
                    echo "<i class='fas fa-trash'></i>";
                    echo "</a>";
                echo "</div>";
            }
            
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p class='text-center text-lg text-gray-600 mt-4'>" . get_string('qcu_not_found', 'mod_peerreview') . "</p>";
}


// Affichage des tcs
echo "<div class='flex mt-8 mx-4 justify-between border-b p-2'>";
    echo "<div class='flex text-center text-gray-500 items-end'>";
        echo "<p class='mr-4 text-4xl font-semibold'> " . count($tcss) . "/" . $session->nbtcs . "</p>";
        echo "<p class='text-3xl'> " . get_string('completed_tcss', 'mod_peerreview') . "</p>";
    echo "</div>";

    if (count($tcss) < $session->nbtcs){
        echo "<a href='qcm_add.php?id={$id}&qcm_type=TCS' class='inline-block px-4 py-2 text-lg font-semibold rounded-2xl bg-lime-300 hover:bg-lime-400 cursor-pointer text-lime-700 no-underline min-w-52'>";
            echo "<i class='fas fa-plus mr-2'></i>";
            echo get_string('add_tcs', 'mod_peerreview');
        echo "</a>";
    }
    
echo "</div>";

if ($tcss) {
    
    echo "<div class='space-y-4 mt-4'>";

    foreach ($tcss as $tcs) {
        $nom_referentiel = isset($referentiels[$tcs->referentiel]) ? $referentiels[$tcs->referentiel] : get_string('unknown', 'mod_peerreview');
        $nom_competency = isset($competencies[$tcs->competency]) ? $competencies[$tcs->competency] : get_string('unknown', 'mod_peerreview');
        $nom_subcompetency = isset($subcompetencies[$tcs->subcompetency]) ? $subcompetencies[$tcs->subcompetency] : get_string('unknown', 'mod_peerreview');

        echo "<div class='p-4 bg-white rounded-3xl shadow flex items-center justify-between'>";

            // Partie gauche (question + infos)
            echo "<div>";
            echo "<p class='font-semibold text-2xl text-gray-700 flex items-center gap-2 mb-4'>";
            echo format_string(ucfirst($tcs->question));
            echo "</p>";

            // Informations sur le référentiel, compétence et sous-compétence
            echo "<div class='mt-2 text-gray-600 text-sm flex flex-col space-y-1'>";

            // Référentiel
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-book text-green-500'></i>";
            echo "<span>" . get_string('referentiel', 'mod_peerreview') . " : <strong>" . ucfirst($nom_referentiel) . "</strong></span>";
            echo "</p>";

            // Compétence
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-bookmark text-orange-500'></i>";
            echo "<span>" . get_string('competency', 'mod_peerreview') . " : <strong>" . ucfirst($nom_competency) . "</strong></span>";
            echo "</p>";

            // Sous-compétence
            echo "<p class='flex items-center gap-2'>";
            echo "<i class='fas fa-award text-purple-500'></i>";
            echo "<span>" . get_string('subcompetency', 'mod_peerreview') . " : <strong>" . ucfirst($nom_subcompetency) . "</strong></span>";
            echo "</p>";

            echo "</div>";
            echo "</div>";

            // Partie droite (boutons)
            if ($tcs->status == 0){
                echo "<div class='flex space-x-2'>";
                    echo "<a href='qcm_edit.php?id={$id}&qcm_id={$tcs->id}' class='px-3 py-2 bg-sky-400 text-white rounded-lg hover:bg-sky-500'>";
                    echo "<i class='fas fa-edit'></i>";
                    echo "</a>";

                    echo "<a href='#' class='px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600' onclick='showDeleteModal({$tcs->id}); return false;'>";
                    echo "<i class='fas fa-trash'></i>";
                    echo "</a>";
                echo "</div>";
            }
        echo "</div>";
    }
    echo "</div>";
} else {
    echo "<p class='text-center text-lg text-gray-600 mt-4'>" . get_string('tcs_not_found', 'mod_peerreview') . "</p>";
}

$completed_pops = 0;
$poptypes = $DB->get_records('pr_question_pop', array('sessionid' => $session->id));
foreach ($poptypes as $poptype) {
    $nbquestions = count(array_filter($questions, fn($q) => $q->poptypeid == $poptype->id));

    if ($poptype->nbqcu + $poptype->nbqcm === $nbquestions) {
        $completed_pops++;
    }
}

// Affichage des pops
echo "<div class='flex mt-8 mx-4 justify-between border-b p-2'>";
    echo "<div class='flex text-center text-gray-500 items-end'>";
        echo "<p class='mr-4 text-4xl font-semibold'> " . $completed_pops . "/" . $session->nbpop . "</p>";
        echo "<p class='text-3xl'> " . get_string('completed_pops', 'mod_peerreview') . "</p>";
    echo "</div>";
    
echo "</div>";

if ($required_pops){

    echo "<div class='space-y-8 mt-4'>";

    foreach ($required_pops as $required_pop) {
        $nbqcm = $required_pop->nbqcm;
        $nbqcu = $required_pop->nbqcu;
        $nb = 1;
        $popTypeId = $required_pop->id;

        // Les POPs de type $PopId créés par l'étudiant courant
        $popsFiltered = array_filter($pops, fn($q) => $q->poptypeid === $popTypeId);

        $qcmText = $nbqcm > 0 ? $nbqcm . " QCM" . ($nbqcm > 1 ? "s" : "") : "";
        $qcuText = $nbqcu > 0 ? $nbqcu . " QCU" . ($nbqcu > 1 ? "s" : "") : "";
        $popText = trim($qcmText . ($qcmText && $qcuText ? ", " : "") . $qcuText);

        $qcmDone = array_filter($popsFiltered, fn($q) => $q->type === "QCM");
        $qcuDone = array_filter($popsFiltered, fn($q) => $q->type === "QCU");

        $pop_completed = (($nbqcm + $nbqcu) === count($popsFiltered)) ? 1 : 0;

        echo "<div class='flex justify-between items-center text-gray-500 mx-16 pb-2 mt-8 border-b'>";
            echo "<div class='flex items-end'>";
                echo "<p class='mr-4 text-2xl font-semibold'> " . $pop_completed . "/" . $nb . "</p>";
                echo "<p class='text-xl'> POP (" . $popText . ") complété </p>";
            echo "</div>";

            echo "<div class='flex space-x-4'>";
                if (count($qcmDone) < $nbqcm){
                    echo "<a href='qcm_add.php?id={$id}&qcm_type=QCM&pop_type_id={$popTypeId}' class='inline-block px-4 py-2 text-lg font-semibold rounded-2xl bg-lime-300 hover:bg-lime-400 cursor-pointer text-lime-700 no-underline min-w-52'>";
                        echo "<i class='fas fa-plus mr-2'></i>";
                        echo get_string('add_qcm', 'mod_peerreview');
                    echo "</a>";
                }

                if (count($qcuDone) < $nbqcu){
                    echo "<a href='qcm_add.php?id={$id}&qcm_type=QCU&pop_type_id={$popTypeId}' class='inline-block px-4 py-2 text-lg font-semibold rounded-2xl bg-lime-300 hover:bg-lime-400 cursor-pointer text-lime-700 no-underline min-w-52'>";
                        echo "<i class='fas fa-plus mr-2'></i>";
                        echo get_string('add_qcu', 'mod_peerreview');
                    echo "</a>";
                }
            echo "</div>";
        echo "</div>";

        $all_questions = array_merge($qcmDone, $qcuDone);

        if (!empty($all_questions)){

            // Tri des résultats par ID (ordre croissant pour POP)
            usort($all_questions, function($a, $b) {
                return $a->id <=> $b->id;
            });

            foreach ($all_questions as $question){

                $nom_referentiel = isset($referentiels[$question->referentiel]) ? $referentiels[$question->referentiel] : get_string('unknown', 'mod_peerreview');
                $nom_competency = isset($competencies[$question->competency]) ? $competencies[$question->competency] : get_string('unknown', 'mod_peerreview');
                $nom_subcompetency = isset($subcompetencies[$question->subcompetency]) ? $subcompetencies[$question->subcompetency] : get_string('unknown', 'mod_peerreview');

                echo "<div class='p-4 bg-white rounded-3xl shadow flex items-center justify-between'>";

                    // Partie gauche (question + infos)
                    echo "<div>";
                    echo "<p class='font-semibold text-2xl text-gray-700 flex items-center gap-2 mb-4'>";
                    echo format_string(ucfirst($question->question));
                    echo "</p>";

                    // Informations sur le référentiel, compétence et sous-compétence
                    echo "<div class='mt-2 text-gray-600 text-sm flex flex-col space-y-1'>";

                    // Référentiel
                    echo "<p class='flex items-center gap-2'>";
                    echo "<i class='fas fa-book text-green-500'></i>";
                    echo "<span>" . get_string('referentiel', 'mod_peerreview') . " : <strong>" . ucfirst($nom_referentiel) . "</strong></span>";
                    echo "</p>";

                    // Compétence
                    echo "<p class='flex items-center gap-2'>";
                    echo "<i class='fas fa-bookmark text-orange-500'></i>";
                    echo "<span>" . get_string('competency', 'mod_peerreview') . " : <strong>" . ucfirst($nom_competency) . "</strong></span>";
                    echo "</p>";

                    // Sous-compétence
                    echo "<p class='flex items-center gap-2'>";
                    echo "<i class='fas fa-award text-purple-500'></i>";
                    echo "<span>" . get_string('subcompetency', 'mod_peerreview') . " : <strong>" . ucfirst($nom_subcompetency) . "</strong></span>";
                    echo "</p>";

                    echo "</div>";
                    echo "</div>";

                    // Partie droite (boutons)
                    if ($question->status == 0){
                        echo "<div class='flex space-x-2'>";
                            echo "<a href='qcm_edit.php?id={$id}&qcm_id={$question->id}' class='px-3 py-2 bg-sky-400 text-white rounded-lg hover:bg-sky-500'>";
                            echo "<i class='fas fa-edit'></i>";
                            echo "</a>";

                            echo "<a href='#' class='px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600' onclick='showDeleteModal({$question->id}); return false;'>";
                            echo "<i class='fas fa-trash'></i>";
                            echo "</a>";
                        echo "</div>";
                    }
                echo "</div>";
            }
        }
        else {
            echo "<p class='text-center text-lg text-gray-600 mt-4'>" . get_string('pop_not_found', 'mod_peerreview') . "</p>";
        }
    }
    echo "</div>";
}


?>

<!-- Modal de confirmation de suppression -->
<div id="delete-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
    <div class="bg-white rounded-3xl py-4 px-16 max-w-lg w-full">
        <div class="flex justify-between items-center">
            <h3 class="text-2xl font-semibold text-red-600"><?php echo get_string('message_delete', 'mod_peerreview'); ?></h3>
            <button id="close-delete-modal" class="text-gray-600 hover:text-gray-800 font-bold text-xl">&times;</button>
        </div>
        <div id="delete-message" class="mt-4 text-gray-700">
            <p><?php echo get_string('confirm_delete', 'mod_peerreview'); ?></p>
        </div>
        <div class="mt-2 text-right">
            <button id="confirm-delete-btn" class="px-4 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 ml-2"><?php echo get_string('delete', 'mod_peerreview'); ?></button>
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

