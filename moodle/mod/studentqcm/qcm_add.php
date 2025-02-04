<?php

// Inclure le fichier de configuration de Moodle pour initialiser l'environnement Moodle
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours depuis l'URL
$id = required_param('id', PARAM_INT);

// Obtenir les informations du module de cours
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

// Vérifier que l'utilisateur est connecté et qu'il a les droits nécessaires
require_login($course, true, $cm);

// Définir l'URL de la page et les informations de la page
$PAGE->set_url('/mod/studentqcm/qcm_list.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

// Charger les fichiers CSS nécessaires
$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

// Afficher l'en-tête de la page
echo $OUTPUT->header();

echo "<div class='mx-auto'>";
echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('create_qcm', 'mod_studentqcm') . "</p>";
echo "</div>";

echo "<div class='flex mt-16 text-lg justify-between'>";
echo "<a href='qcm_list.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
echo "<i class='fas fa-arrow-left mr-2'></i>";
echo get_string('back', 'mod_studentqcm');
echo "</a>";
echo "</div>";

// Formulaire
echo "<form method='post' action='submit_qcm.php?id={$id}'>";
echo "<div class='mt-8'>";

    // Référentiel, compétence et sous-compétence
    echo "<div class='grid grid-cols-3 gap-4'>";

        // Sélection du référentiel
        echo "<div class='rounded-3xl bg-lime-200 mb-2 p-4'>";
        echo "<label for='referentiel_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('referentiel', 'mod_studentqcm') . " :</label>";
        echo "<select id='referentiel_1' name='questions[1][referentiel]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg'>";
        $referentiels = $DB->get_records('referentiel');
        foreach ($referentiels as $referentiel) {
            echo "<option value='{$referentiel->id}'>{$referentiel->name}</option>";
        }
        echo "</select>";
        echo "</div>";

        // Sélection de la compétence
        echo "<div class='rounded-3xl bg-lime-200 mb-2 p-4'>";
        echo "<label for='competency_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('competency', 'mod_studentqcm') . " :</label>";
        echo "<select id='competency_1' name='questions[1][competency]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg'>";
        $competencies = $DB->get_records('competency');
        foreach ($competencies as $competency) {
            echo "<option value='{$competency->id}'>{$competency->name}</option>";
        }
        echo "</select>";
        echo "</div>";

        // Sélection de la sous-compétence
        echo "<div class='rounded-3xl bg-lime-200 mb-2 p-4'>";
        echo "<label for='subcompetency_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('subcompetency', 'mod_studentqcm') . " :</label>";
        echo "<select id='subcompetency_1' name='questions[1][subcompetency]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg'>";
        $subcompetencies = $DB->get_records('subcompetency');
        foreach ($subcompetencies as $subcompetency) {
            echo "<option value='{$subcompetency->id}'>{$subcompetency->name}</option>";
        }
        echo "</select>";
        echo "</div>";

    echo "</div>";

    // Mots-clés
    echo "<div class='rounded-3xl bg-lime-200 my-2 p-4'>";
    echo "<label for='keywords_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('keywords', 'mod_studentqcm') . " :</label>";
    $keywords = $DB->get_records('keyword');
    foreach ($keywords as $keyword) {
        echo "<div class='flex items-center'>";
        echo "<input type='checkbox' id='keyword_1_{$keyword->id}' name='questions[1][keywords][]' value='{$keyword->id}' class='mr-2'>";
        echo "<label for='keyword_1_{$keyword->id}' class='text-gray-700'>{$keyword->word}</label>";
        echo "</div>";
    }
    echo "</div>";

    // Question
    echo "<div class='rounded-3xl bg-indigo-200 my-4 p-4'>";
    echo "<label for='question_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('question', 'mod_studentqcm') . " 1:</label>";
    echo "<input type='text' id='question_1' name='questions[1][question]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg' required>";
    echo "</div>";

    // Réponses
    for ($i = 1; $i <= 5; $i++) {
        echo "<div class='rounded-3xl bg-sky-100 my-2 p-4'>";

        // Réponse
        echo "<div class='py-2 grid grid-cols-12 w-full'>";
        echo "<label for='answer_1_{$i}' class='col-span-2 block font-semibold text-gray-700 text-lg'>" . get_string('answer', 'mod_studentqcm') . " $i :</label>";
        echo "<div class='col-span-10 w-full'>";
        echo "<textarea id='answer_1_{$i}' name='questions[1][answers][{$i}][answer]' class='w-full block resize-none p-2 mt-2 border border-gray-300 rounded-lg' required></textarea>";
        echo "</div>";
        echo "</div>";

        // Explication
        echo "<div class='py-2 grid grid-cols-12 w-full'>";
        echo "<label for='explanation_1_{$i}' class='col-span-2 block font-semibold text-gray-700 text-lg'>" . get_string('explanation', 'mod_studentqcm') . " $i :</label>";
        echo "<div class='col-span-10 w-full'>";
        echo "<textarea id='explanation_1_{$i}' name='questions[1][answers][{$i}][explanation]' class='w-full block resize-none p-2 mt-2 border border-gray-300 rounded-lg' required></textarea>";
        echo "</div>";
        echo "</div>";

        // Champ checkbox pour marquer la réponse correcte
        echo "<div class='py-2 grid grid-cols-12 w-full'>";
        echo "<label for='correct_answer_1_{$i}' class='col-span-2 block font-semibold text-gray-700 text-lg'>" . get_string('correct_answer', 'mod_studentqcm') . " ?</label>";
        echo "<div class='col-span-10 w-full flex items-center'>";
        echo "<input type='checkbox' id='correct_answer_1_{$i}' name='questions[1][answers][{$i}][correct]' value='1' class='mr-2 h-4 w-4'>";
        echo "</div>";
        echo "</div>";

        echo "</div>";
    }


echo "</div>";

echo "<div class='mb-4 mt-4 flex justify-end'>";
echo "<button type='submit' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-lime-200 hover:bg-lime-300 cursor-pointer text-lime-700 no-underline text-lg'>" . get_string('submit', 'mod_studentqcm') . "</button>";
echo "</div>";  

echo "</form>";

echo "<script src='https://cdn.jsdelivr.net/npm/tinymce@6.8.0/tinymce.min.js'></script>";
echo "<script>
    tinymce.init({
    selector: 'textarea',
    plugins: ['image', 'media', 'link', 'table'],
    toolbar: 'undo redo | bold italic underline | image media | link | table | uploadimage',
    image_advtab: true,
    media_dimensions: true,
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


</script>";

echo $OUTPUT->footer();
