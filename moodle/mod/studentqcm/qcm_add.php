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

echo "<form method='post' action='submit_qcm.php'>";
echo "<div class='mt-8'>";

$question_count = 3;

for ($q = 1; $q <= $question_count; $q++) {
    // Question
    echo "<div class='rounded-3xl bg-indigo-200 mb-2 p-4 mt-16'>";
    echo "<label for='question_$q' class='block font-semibold text-gray-700 text-lg'>" . get_string('question', 'mod_studentqcm') . " $q :</label>";
    echo "<input type='text' id='question_$q' name='questions[$q][question]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg' required>";
    echo "</div>";

    // Réponses
    for ($i = 1; $i <= 5; $i++) {
        echo "<div class='rounded-3xl bg-sky-100 my-2 p-4'>";
        
        // Réponse
        echo "<div class='py-2 grid grid-cols-12 w-full'>";
        echo "<label for='answer_{$q}_{$i}' class='col-span-2 block font-semibold text-gray-700 text-lg'>" . get_string('answer', 'mod_studentqcm') . " $i :</label>";
        echo "<div class='col-span-10 w-full'>";
        echo "<textarea id='answer_{$q}_{$i}' name='questions[{$q}][answers][{$i}][answer]' class='w-full block resize-none p-2 mt-2 border border-gray-300 rounded-lg' required></textarea>";
        echo "</div>";
        echo "</div>";

        // Explication
        echo "<div class='py-2 grid grid-cols-12 w-full'>";
        echo "<label for='explanation_{$q}_{$i}' class='col-span-2 block font-semibold text-gray-700 text-lg'>" . get_string('explanation', 'mod_studentqcm') . " $i :</label>";
        echo "<div class='col-span-10 w-full'>";
        echo "<textarea id='explanation_{$q}_{$i}' name='questions[{$q}][answers][{$i}][explanation]' class='w-full block resize-none p-2 mt-2 border border-gray-300 rounded-lg' required></textarea>";
        echo "</div>";
        echo "</div>";

        // Champ checkbox pour marquer la réponse correcte
        echo "<div class='py-2 grid grid-cols-12 w-full'>";
        echo "<label for='correct_answer_{$q}_{$i}' class='col-span-2 block font-semibold text-gray-700 text-lg'>" . get_string('correct_answer', 'mod_studentqcm') . " ?</label>";
        echo "<div class='col-span-10 w-full flex items-center'>";
        echo "<input type='checkbox' id='correct_answer_{$q}_{$i}' name='questions[{$q}][answers][{$i}][correct]' value='1' class='mr-2 h-4 w-4'>";
        echo "</div>";
        echo "</div>";

        echo "</div>";
    }
}

echo "</div>";

echo "<div class='mb-4 flex justify-end'>";
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

        // Activer l'upload d'images
        images_upload_url: 'upload.php', // Fichier serveur pour gérer l'upload
        automatic_uploads: true,
        
        // Activer le bouton d'upload
        file_picker_callback: function(callback, value, meta) {
            if (meta.filetype === 'image') {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.onchange = function() {
                    var file = this.files[0];
                    var reader = new FileReader();
                    
                    reader.onload = function () {
                        var base64 = reader.result.split(',')[1];
                        callback('data:image/png;base64,' + base64, {alt: file.name});
                    };
                    
                    reader.readAsDataURL(file);
                };
                input.click();
            }
        }
    });
</script>";


echo $OUTPUT->footer();