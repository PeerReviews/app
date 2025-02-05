<?php

// Inclure le fichier de configuration de Moodle pour initialiser l'environnement Moodle
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours depuis l'URL
$id = required_param('id', PARAM_INT);  // ID du module de cours
$qcm_id = required_param('qcm_id', PARAM_INT); // ID du QCM à modifier

// Obtenir les informations du module de cours
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

// Vérifier que l'utilisateur est connecté et qu'il a les droits nécessaires
require_login($course, true, $cm);

// Récupérer le QCM à modifier
$qcm = $DB->get_record('studentqcm_question', array('id' => $qcm_id), '*', MUST_EXIST);

// Vérifier que l'utilisateur est bien le créateur du QCM
if ($qcm->userid != $USER->id) {
    print_error('unauthorized', 'mod_studentqcm');
}

// Récupérer les réponses et les explications associées à la question
$answers = $DB->get_records('studentqcm_answer', array('question_id' => $qcm_id));

// Charger les données supplémentaires
$referentiels = $DB->get_records('referentiel');
$competencies = $DB->get_records('competency');
$subcompetencies = $DB->get_records('subcompetency');

// Définir l'URL de la page et les informations de la page
$PAGE->set_url('/mod/studentqcm/qcm_edit.php', array('id' => $id, 'qcm_id' => $qcm_id));
$PAGE->set_title(get_string('edit_qcm', 'mod_studentqcm'));
$PAGE->set_heading(format_string($course->fullname));

// Charger les fichiers CSS nécessaires
$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('edit_qcm', 'mod_studentqcm') . "</p>";
echo "</div>";

echo "<div class='flex mt-8 text-lg justify-between'>";
echo "<a href='qcm_list.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
echo "<i class='fas fa-arrow-left mr-2'></i>";
echo get_string('back', 'mod_studentqcm');
echo "</a>";
echo "</div>";

?>

<!-- Formulaire de modification -->
<form method="post" action="qcm_edit_process.php">
    <div class='mt-8'>
        <input type="hidden" name="id" value="<?= $id ?>">
        <input type="hidden" name="qcm_id" value="<?= $qcm->id ?>">

        <div class='grid grid-cols-3 gap-4'>
            <!-- Référentiel -->
            <div class='rounded-3xl bg-lime-200 mb-2 p-4'>
                <label for="referentiel" class="block font-semibold text-gray-700 text-lg">
                    <?php echo get_string('referentiel', 'mod_studentqcm'); ?> :
                </label>
                <select name="referentiel" id="referentiel" class="w-full p-2 mt-2 border border-gray-300 rounded-lg">
                    <?php foreach ($referentiels as $referentiel): ?>
                        <option value="<?= $referentiel->id ?>" <?= ($qcm->referentiel == $referentiel->id) ? 'selected' : '' ?>><?= $referentiel->name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Compétences -->
            <div class='rounded-3xl bg-lime-200 mb-2 p-4'>
                <label for="competency" class="block font-semibold text-gray-700 text-lg">
                    <?php echo get_string('competency', 'mod_studentqcm'); ?> :
                </label>
                <select name="competency" id="competency" class="w-full p-2 mt-2 border border-gray-300 rounded-lg">
                    <?php foreach ($competencies as $competency): ?>
                        <option value="<?= $competency->id ?>" <?= ($qcm->competency == $competency->id) ? 'selected' : '' ?>><?= $competency->name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sous-compétences -->
            <div class='rounded-3xl bg-lime-200 mb-2 p-4'>
                <label for="subcompetency" class="block font-semibold text-gray-700 text-lg">
                    <?php echo get_string('subcompetency', 'mod_studentqcm'); ?> :
                </label>
                <select name="subcompetency" id="subcompetency" class="w-full p-2 mt-2 border border-gray-300 rounded-lg">
                    <?php foreach ($subcompetencies as $subcompetency): ?>
                        <option value="<?= $subcompetency->id ?>" <?= ($qcm->subcompetency == $subcompetency->id) ? 'selected' : '' ?>><?= $subcompetency->name ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class='rounded-3xl bg-lime-200 my-2 p-4'>
            <label for="keywords" class="block font-semibold text-gray-700 text-lg">
                <?php echo get_string('keywords', 'mod_studentqcm'); ?> :
            </label>
            <?php
            $keywords = $DB->get_records('keyword');
            $selected_keywords = $DB->get_records_menu('question_keywords', ['question_id' => $qcm_id], '', 'keyword_id, keyword_id');

            foreach ($keywords as $keyword) {
                $escapedWord = htmlspecialchars($keyword->word, ENT_QUOTES, 'UTF-8');
                $checked = isset($selected_keywords[$keyword->id]) ? "checked" : ""; // Vérifie si le keyword est sélectionné

                echo "<div class='flex items-center'>";
                echo "<input type='checkbox' id='keyword_{$keyword->id}' name='questions[1][keywords][]' value='{$keyword->id}' class='mr-2' {$checked}>";
                echo "<label for='keyword_{$keyword->id}' class='text-gray-700'>{$escapedWord}</label>";
                echo "</div>";
            }
            ?>
        </div>



        <!-- Context -->
        <div class='rounded-3xl bg-indigo-200 my-4 p-4'>
            <label for="context" class="block font-semibold text-gray-700 text-lg">
                <?php echo get_string('context', 'mod_studentqcm'); ?> :
            </label>
            <input type='text' id='context_1' name='questions[1][context]' value="<?= $qcm->context ?>" class='w-full p-2 mt-2 border border-gray-300 rounded-lg' required>
        </div>

        <!-- Question -->
        <div class='rounded-3xl bg-indigo-200 my-4 p-4'>
            <label for="context" class="block font-semibold text-gray-700 text-lg">
                <?php echo get_string('question', 'mod_studentqcm'); ?> :
            </label>
            <input type='text' id='context_1' name='questions[1][question]' value="<?= $qcm->question ?>" class='w-full p-2 mt-2 border border-gray-300 rounded-lg' required>
        </div>

        <!-- Parcours des réponses existantes -->
        <?php $counter = 1; ?>
        <?php foreach ($answers as $index => $answer): ?>
            <div class='rounded-3xl bg-sky-100 my-2 p-4'>
                <div class='py-2 grid grid-cols-12 w-full'>
                    <label for="answer_<?= $index ?>" class="col-span-2 block font-semibold text-gray-700 text-lg"><?= get_string('answer', 'mod_studentqcm') ?> <?= $counter ?></label>
                    <div class='col-span-10 w-full'>
                        <textarea name="answer_<?= $index ?>" id="answer_<?= $index ?>" value="<?= $answer->answer ?>" class="w-full p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required><?= $answer->answer ?></textarea>
                    </div>
                </div>

                <div class='py-2 grid grid-cols-12 w-full'>
                    <label for="explanation_<?= $index ?>" class="col-span-2 block font-semibold text-gray-700 text-lg"><?= get_string('explanation', 'mod_studentqcm') ?> <?= $counter ?></label>
                    <div class='col-span-10 w-full'>
                        <textarea name="explanation_<?= $index ?>" id="explanation_<?= $index ?>" value="<?= $answer->explanation ?>" class="w-full p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required><?= $answer->explanation ?></textarea>
                    </div>
                </div>

                <div class='py-2 grid grid-cols-12 w-full'>
                    <label for="correct_answer_<?= $index ?>" class="col-span-2 block font-semibold text-gray-700 text-lg"><?= get_string('correct_answer', 'mod_studentqcm') ?> <?= $counter ?> ? </label>
                    <div class='col-span-10 w-full flex items-center'>
                        <label class='relative inline-flex items-center cursor-pointer'>
                            <input type='checkbox' id='correct_answer_<?= $index ?>' name='correct_answer_<?= $index ?>' value='1' class='sr-only peer' <?= $answer->istrue == 1 ? 'checked' : '' ?>>
                            <span class='w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-lime-400 peer-checked:after:translate-x-full peer-checked:after:bg-white after:content-"" after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all'></span>
                        </label>
                    </div>
                </div>
            </div>
            <?php $counter++; ?>
        <?php endforeach; ?>

        <div class='rounded-3xl bg-indigo-200 my-4 p-4'>
            <label for="global_comment" class="block font-semibold text-gray-700 text-lg">
                <?php echo get_string('global_comment', 'mod_studentqcm'); ?> :
            </label>
            <input type='text' id='global_comment' name='questions[1][global_comment]' value="<?= $qcm->global_comment ?>" class='w-full p-2 mt-2 border border-gray-300 rounded-lg' required>
        </div>

    </div>

    <!-- Bouton de soumission -->
    <div class="mb-4 mt-4 flex justify-end">
        <button type="submit" class="inline-block px-4 py-2 font-semibold rounded-2xl bg-lime-200 hover:bg-lime-300 cursor-pointer text-lime-700 no-underline text-lg"><?= get_string('save_changes', 'mod_studentqcm') ?></button>
    </div>
</form>

<?php
echo "<script src='https://cdn.jsdelivr.net/npm/tinymce@6.8.0/tinymce.min.js'></script>";
?>
<script>
    tinymce.init({
        selector: 'textarea',
        plugins: 'image media link table',
        toolbar: 'undo redo | bold italic underline | image media | link table',
        height: 180,
        images_upload_url: 'upload.php',
        automatic_uploads: true,
        file_picker_callback: function(callback, value, meta) {
            if (meta.filetype === 'image') {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.onchange = function() {
                    var file = this.files[0];
                    var reader = new FileReader();
                    reader.onload = function() {
                        var base64 = reader.result.split(',')[1];
                        callback('data:image/png;base64,' + base64, {alt: file.name});
                    };
                    reader.readAsDataURL(file);
                };
                input.click();
            }
        },
        setup: function (editor) {
            editor.on('init', function () {
                editor.getContainer().closest('form').setAttribute('novalidate', true);
            });
        }
    });
</script>

<?php
echo $OUTPUT->footer();
?>

