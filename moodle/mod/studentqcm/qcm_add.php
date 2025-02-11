<?php

// Inclure le fichier de configuration de Moodle pour initialiser l'environnement Moodle
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours depuis l'URL
$id = required_param('id', PARAM_INT);
$type = required_param('qcm_type', PARAM_TEXT);

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

echo "<div class='flex mt-8 text-lg justify-between'>";
echo "<a href='qcm_list.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
echo "<i class='fas fa-arrow-left mr-2'></i>";
echo get_string('back', 'mod_studentqcm');
echo "</a>";
echo "</div>";

// Formulaire
echo "<form method='post' action='submit_qcm.php?id={$id}&type={$type}'>";
echo "<div class='mt-8'>";

    // Référentiel, compétence et sous-compétence
    echo "<div class='grid grid-cols-3 gap-4'>";

        // Sélection du référentiel
        echo "<div class='rounded-3xl bg-lime-200 mb-2 p-4'>";
        echo "<label for='referentiel_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('referentiel', 'mod_studentqcm') . " :</label>";
        echo "<select id='referentiel_1' name='questions[1][referentiel]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg'>";
        echo "<option value=''>Sélectionnez un référentiel</option>";
        $referentiels = $DB->get_records('referentiel');
        foreach ($referentiels as $referentiel) {
            echo "<option value='{$referentiel->id}'>{$referentiel->name}</option>";
        }
        echo "</select>";
        echo "</div>";

        // Sélection de la compétence
        echo "<div class='rounded-3xl bg-lime-200 mb-2 p-4'>";
        echo "<label for='competency_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('competency', 'mod_studentqcm') . " :</label>";
        echo "<select id='competency_1' name='questions[1][competency]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg' disabled>";
        echo "<option value=''>Sélectionnez une compétence</option>";
        echo "</select>";
        echo "</div>";

        // Sélection de la sous-compétence
        echo "<div class='rounded-3xl bg-lime-200 mb-2 p-4'>";
        echo "<label for='subcompetency_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('subcompetency', 'mod_studentqcm') . " :</label>";
        echo "<select id='subcompetency_1' name='questions[1][subcompetency]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg' disabled>";
        echo "<option value=''>Sélectionnez une sous-compétence </option>";
        echo "</select>";
        echo "</div>";

    echo "</div>";

    // Sélection des mots clés
    echo "<div class='rounded-3xl bg-lime-200 my-2 p-4'>";
    echo "<label class='block font-semibold text-gray-700 text-lg'>" . get_string('keywords', 'mod_studentqcm') . " :</label>";
    echo "<div id='keywords_list_1'>";
    echo "<p class='text-gray-500'>Sélectionnez une sous-compétence pour voir les mots-clés.</p>";
    echo "</div>";
    echo "</div>";

    // Context
    echo "<div class='rounded-3xl bg-indigo-200 my-4 p-4'>";
    echo "<label for='context_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('context', 'mod_studentqcm') . " :</label>";
    echo "<textarea id='context_1' name='questions[1][context]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg' required rows='5'></textarea>";
    echo "</div>";

    // Question
    echo "<div class='rounded-3xl bg-indigo-200 my-4 p-4'>";
    echo "<label for='question_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('question', 'mod_studentqcm') . " :</label>";
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

        if($type != "TCS") {
            // Explication
            echo "<div class='py-2 grid grid-cols-12 w-full'>";
            echo "<label for='explanation_1_{$i}' class='col-span-2 block font-semibold text-gray-700 text-lg'>" . get_string('explanation', 'mod_studentqcm') . " $i :</label>";
            echo "<div class='col-span-10 w-full'>";
            echo "<textarea id='explanation_1_{$i}' name='questions[1][answers][{$i}][explanation]' class='w-full block resize-none p-2 mt-2 border border-gray-300 rounded-lg' required></textarea>";
            echo "</div>";
            echo "</div>";
        }

        // Champ checkbox pour marquer la réponse correcte
        echo "<div class='py-2 grid grid-cols-12 w-full'>";
        echo "<label for='correct_answer_1_{$i}' class='col-span-2 block font-semibold text-gray-700 text-lg'>" . get_string('correct_answer', 'mod_studentqcm') . " ?</label>";
        echo "<div class='col-span-10 w-full flex items-center'>";
        echo "<label class='relative inline-flex items-center cursor-pointer'>";
        echo "<input type='checkbox' id='correct_answer_1_{$i}' name='questions[1][answers][{$i}][correct]' value='1' class='sr-only peer'>";
        echo "<span class='w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-lime-400 peer-checked:after:translate-x-full peer-checked:after:bg-white after:content-\"\" after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all'></span>";
        echo "</label>";
        echo "</div>";
        echo "</div>";

        echo "</div>";
    }

    // Commentaire / explication globale
    echo "<div class='rounded-3xl bg-indigo-200 my-4 p-4'>";
    echo "<label for='global_comment' class='block font-semibold text-gray-700 text-lg'>" . get_string('global_comment', 'mod_studentqcm') . " :</label>";
    echo "<input type='text' id='global_comment' name='questions[1][global_comment]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg' required>";
    echo "</div>";


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

?>

<!-- Modal for error messages -->
<div id="error-modal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
    <div class="bg-white rounded-3xl py-4 px-16 max-w-lg w-full">
        <div class="flex justify-between items-center">
            <h3 class="text-2xl font-semibold text-red-600">Erreur de validation</h3>
            <button id="close-modal" class="text-gray-600 hover:text-gray-800 font-bold text-xl">&times;</button>
        </div>
        <div id="error-messages" class="mt-4 text-gray-700"></div>
        <div class="mt-2 text-right">
            <button id="close-modal-btn" class="px-4 py-2 bg-red-600 text-white rounded-full hover:bg-red-700">Fermer</button>
        </div>
    </div>
</div>

<?php
echo $OUTPUT->footer();

?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Filtrer les compétences en fonction du référentiel sélectionné
    $('#referentiel_1').change(function() {
        var referentielId = $(this).val();
        $('#competency_1').html('<option value="">Chargement...</option>').prop('disabled', true);
        $('#subcompetency_1').html('<option value="" disabled selected>Sélectionnez une sous-compétence</option>').prop('disabled', true);

        if (referentielId) {
            $.get('fetch_competencies.php', { referentiel_id: referentielId }, function(data) {
                $('#competency_1').html(data).prop('disabled', false).trigger('change'); // Déclenche un événement 'change' pour charger les sous-compétences
            });
        }
    });

    // Mettre à jour les sous-compétences en fonction de la compétence sélectionnée
    $('#competency_1').on('change', function() {
        var competencyId = $(this).val();
        $('#subcompetency_1').html('<option value="" disabled selected>Chargement...</option>').prop('disabled', true);

        if (competencyId) {
            $.getJSON('fetch_subcompetencies.php', { competency_id: competencyId }, function(data) {
                if (data.length > 0) {
                    var options = '<option value="" disabled selected>Sélectionnez une sous-compétence</option>';
                    $.each(data, function(index, subcompetency) {
                        options += `<option value="${subcompetency.id}">${subcompetency.name}</option>`;
                    });
                    $('#subcompetency_1').html(options).prop('disabled', false);
                } else {
                    $('#subcompetency_1').html('<option value="" disabled selected>Aucune sous-compétence disponible</option>').prop('disabled', true);
                }
            }).fail(function() {
                // En cas d'erreur AJAX, on désactive la liste déroulante
                $('#subcompetency_1').html('<option value="" disabled selected>Erreur de chargement</option>').prop('disabled', true);
            });
        }
    });

    // Mise à jour de la liste des mots-clés pour la sous-compétence sélectionnée
    $('#subcompetency_1').change(function() {
        var subcompetencyId = $(this).val();
        $('#keywords_list_1').html('<p class="text-gray-500">Chargement...</p>');

        if (subcompetencyId) {
            // Utilisation de $.getJSON() pour récupérer les mots-clés
            $.getJSON('fetch_keywords.php', { subcompetency_id: subcompetencyId }, function(data) {
                if (data.length > 0) {
                    var checkboxes = '';
                    $.each(data, function(index, keyword) {
                        checkboxes += `
                            <div class="flex items-center mb-2">
                                <input type="checkbox" id="keyword_${keyword.id}" name="questions[1][keywords][]" value="${keyword.id}" class="mr-2">
                                <label for="keyword_${keyword.id}" class="text-gray-700">${keyword.word}</label>
                            </div>
                        `;
                    });
                    $('#keywords_list_1').html(checkboxes); // Mettre à jour le div avec les checkboxes
                } else {
                    $('#keywords_list_1').html('<p class="text-gray-500">Aucun mot-clé disponible</p>');
                }
            }).fail(function() {
                // En cas d'erreur AJAX, on affiche un message d'erreur
                $('#keywords_list_1').html('<p class="text-red-500">Erreur de chargement des mots-clés</p>');
            });
        }
    });



    // Validation avant soumission du formulaire
    $('form').on('submit', function(e) {
        var isValid = true;
        var errorMessage = '';
        var urlParams = new URLSearchParams(window.location.search);
        var qcmType = urlParams.get('qcm_type');

        // Réinitialiser la liste d'erreurs
        $('#error-messages').empty();

        // Vérification du référentiel
        if ($('#referentiel_1').val() === "") {
            isValid = false;
            $('#error-messages').append('<li>Le référentiel est requis.</li>');
        }

        // Vérification de la compétence
        if ($('#competency_1').val() === "") {
            isValid = false;
            $('#error-messages').append('<li>La compétence est requise.</li>');
        }

        // Vérification de la sous-compétence
        if ($('#subcompetency_1').val() === "" || $('#subcompetency_1').val() === null) {
            isValid = false;
            $('#error-messages').append('<li>La sous-compétence est requise.</li>');
        }

        // Vérification des mots-clés (au moins un mot-clé doit être sélectionné)
        if ($('input[name="questions[1][keywords][]"]:checked').length === 0) {
            isValid = false;
            $('#error-messages').append('<li>Au moins un mot-clé doit être sélectionné.</li>');
        }

        // Vérification du champ contexte
        if ($('#context_1').val().trim() === "") {
            isValid = false;
            $('#error-messages').append('<li>Le contexte est requis.</li>');
        }

        // Vérification de la question
        if ($('#question_1').val().trim() === "") {
            isValid = false;
            $('#error-messages').append('<li>La question est requise.</li>');
        }

        // Vérification des réponses (si le type est QCU, il doit y avoir uniquement une réponse correcte)
        if (qcmType === "QCU" || qcmType === "TCS") {
            var correctAnswers = 0;
            for (var i = 1; i <= 5; i++) {
                if ($('#correct_answer_1_' + i).prop('checked')) {
                    correctAnswers++;
                }
            }

            // Si plus d'une réponse correcte, on affiche un message d'erreur
            if (correctAnswers !== 1) {
                isValid = false;
                $('#error-messages').append('<li>Il doit y avoir exactement une réponse correcte pour les questions de type QCU.</li>');
            }
        } else {
            // Vérification des réponses pour les autres types de QCM (par exemple, QCM à choix multiples)
            var atLeastOneCorrectAnswer = false;
            for (var i = 1; i <= 5; i++) {
                if ($('#correct_answer_1_' + i).prop('checked')) {
                    atLeastOneCorrectAnswer = true;
                    break;
                }
            }

            if (!atLeastOneCorrectAnswer) {
                isValid = false;
                $('#error-messages').append('<li>Au moins une réponse doit être correcte.</li>');
            }
        }

        // Vérification du commentaire global
        if ($('#global_comment').val().trim() === "") {
            isValid = false;
            $('#error-messages').append('<li>Le commentaire global est requis.</li>');
        }

        // Si la validation échoue, on empêche la soumission et on affiche un message d'erreur dans le modal
        if (!isValid) {
            e.preventDefault();

            // Afficher les messages d'erreur dans le modal
            $('#error-modal').removeClass('hidden');
        }
    });

    // Fermer le modal
    $('#close-modal, #close-modal-btn').on('click', function() {
        $('#error-modal').addClass('hidden');
    });
});
</script>
