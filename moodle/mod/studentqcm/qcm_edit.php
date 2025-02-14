<?php

// Inclure le fichier de configuration de Moodle pour initialiser l'environnement Moodle
require_once(__DIR__ . '/../../config.php');

// Récupérer l'ID du module de cours depuis l'URL
$id = required_param('id', PARAM_INT);
$qcm_id = required_param('qcm_id', PARAM_INT);

// Obtenir les informations du module de cours
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

// Vérifier que l'utilisateur est connecté et qu'il a les droits nécessaires
require_login($course, true, $cm);

$question = $DB->get_record('studentqcm_question', array('id' => $qcm_id), '*', MUST_EXIST);
$type = $question->type;
$answers = $DB->get_records('studentqcm_answer', array('question_id' => $qcm_id));

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
echo "<form method='post' action='qcm_edit_process.php?id={$id}&qcm_id={$qcm_id}&type={$type}'>";
echo "<div class='mt-8'>";

    // Référentiel, compétence et sous-compétence
    echo "<div class='grid grid-cols-1 lg:grid-cols-3 gap-4'>";

        // Sélection du référentiel
        echo "<div class='rounded-3xl bg-lime-200 mb-2 p-4'>";
        echo "<label for='referentiel_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('referentiel', 'mod_studentqcm') . " :</label>";
        echo "<select id='referentiel_1' name='questions[1][referentiel]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg'>";
        echo "<option value=''>" . get_string('select_referentiel', 'mod_studentqcm') . "</option>";
        $referentiels = $DB->get_records('referentiel');
        foreach ($referentiels as $referentiel) {
            $selected = ($question->referentiel == $referentiel->id) ? 'selected' : '';
            echo "<option value='{$referentiel->id}' {$selected}>{$referentiel->name}</option>";
        }
        echo "</select>";
        echo "</div>";

        // Sélection de la compétence
        echo "<div class='rounded-3xl bg-lime-200 mb-2 p-4'>";
        echo "<label for='competency_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('competency', 'mod_studentqcm') . " :</label>";
        echo "<select id='competency_1' name='questions[1][competency]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg' disabled>";
        echo "<option value=''>" . get_string('select_competency', 'mod_studentqcm') . "</option>";
        $competencies = $DB->get_records('competency');
        foreach ($competencies as $competency) {
            $selected = ($question->competency == $competency->id) ? 'selected' : '';
            echo "<option value='{$competency->id}' {$selected}>{$competency->name}</option>";
        }
        echo "</select>";
        echo "</div>";

        // Sélection de la sous-compétence avec bouton + pour ajouter une nouvelle sous-compétence
        echo "<div class='rounded-3xl bg-lime-200 mb-2 p-4'>";
        echo "<label for='subcompetency_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('subcompetency', 'mod_studentqcm') . " :</label>";
        echo "<div class='flex items-center space-x-2'>";
        echo "<select id='subcompetency_1' name='questions[1][subcompetency]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg' disabled>";
        echo "<option value=''>" . get_string('select_subcompetency', 'mod_studentqcm') . "</option>";
        $subcompetencies = $DB->get_records('subcompetency');
        foreach ($subcompetencies as $subcompetency) {
            $selected = ($question->subcompetency == $subcompetency->id) ? 'selected' : '';
            echo "<option value='{$subcompetency->id}' {$selected}>{$subcompetency->name}</option>";
        }
        echo "</select>";
        echo "<button type='button' id='show_new_subcompetency' class='flex items-center justify-center w-9 h-9 mt-2 bg-lime-400 text-white rounded-lg hover:bg-lime-500'>";
        echo "<i class='fas fa-plus'></i>";
        echo "</button>";
        echo "</div>";


        // Champ de saisie pour ajouter une nouvelle sous-compétence (initialement masqué)
        echo "<div id='new_subcompetency_field' class='mt-2 hidden flex items-center space-x-2'>";
        echo "<input type='text' id='new_subcompetency' name='new_subcompetency' class='w-full px-3 py-2 border border-gray-300 rounded-lg' placeholder='Ajoutez une sous-compétence'>";
        echo "<button type='button' id='add_new_subcompetency' class='px-3 py-2 font-semibold rounded-lg bg-lime-400 hover:bg-lime-500 text-white'>";
        echo get_string('add', 'mod_studentqcm');
        echo "</button>";
        echo "</div>";

        echo "</div>";

    echo "</div>";

    // Sélection des mots clés
    echo "<div class='rounded-3xl bg-lime-200 my-2 p-4'>";
    echo "<label class='block font-semibold text-gray-700 text-lg'>" . get_string('keywords', 'mod_studentqcm') . " :</label>";
    echo "<div id='keywords_list_1' class='flex flex-wrap gap-4 items-center'>";
    echo "<p class='text-gray-500 col-span-6'>Sélectionnez une sous-compétence pour voir les mots-clés.</p>";
    echo "</div>";

    // Champ de saisie pour ajouter un nouveau mot-clé (initialement masqué)
    echo "<div id='new_keyword_field' class='mt-2 hidden flex items-center space-x-2'>";
    echo "<input type='text' id='new_keyword' name='new_keyword' class='w-full px-3 py-2 border border-gray-300 rounded-lg' placeholder='Ajoutez un mot-clé'>";
    echo "<button type='button' id='add_new_keyword' class='px-3 py-2 font-semibold rounded-lg bg-lime-400 hover:bg-lime-500 text-white'>";
    echo get_string('add', 'mod_studentqcm');
    echo "</button>";
    echo "</div>";

    echo "</div>";


    // Context
    echo "<div class='rounded-3xl bg-indigo-200 my-4 p-4'>";
    echo "<label for='context_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('context', 'mod_studentqcm') . " :</label>";
    echo "<textarea id='context_1' name='questions[1][context]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg' required rows='5'>{$question->context}</textarea>";
    echo "</div>";


    // Question
    echo "<div class='rounded-3xl bg-indigo-200 my-4 p-4'>";
    echo "<label for='question_1' class='block font-semibold text-gray-700 text-lg'>" . get_string('question', 'mod_studentqcm') . " :</label>";
    echo "<input type='text' id='question_1' name='questions[1][question]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg' required value='{$question->question}'>";
    echo "</div>";


    // Réponses
    $counter = 1;
    foreach ($answers as $index => $answer) {
        echo "<div class='rounded-3xl bg-sky-100 my-2 p-4'>";
    
        // Réponse
        echo "<div class='py-2 grid grid-cols-12 w-full'>";
        echo "<label for='answer_1_{$index}' class='col-span-2 block font-semibold text-gray-700 text-lg'>" . get_string('answer', 'mod_studentqcm') . " $counter :</label>";
        echo "<div class='col-span-10 w-full'>";
        echo "<textarea id='answer_1_{$index}' name='questions[1][answers][{$index}][answer]' class='w-full block resize-none p-2 mt-2 border border-gray-300 rounded-lg' required>{$answer->answer}</textarea>";
        echo "</div>";
        echo "</div>";
    
        if($type != "TCS") {
            // Explication
            echo "<div class='py-2 grid grid-cols-12 w-full'>";
            echo "<label for='explanation_1_{$index}' class='col-span-2 block font-semibold text-gray-700 text-lg'>" . get_string('explanation', 'mod_studentqcm') . " $counter :</label>";
            echo "<div class='col-span-10 w-full'>";
            echo "<textarea id='explanation_1_{$index}' name='questions[1][answers][{$index}][explanation]' class='w-full block resize-none p-2 mt-2 border border-gray-300 rounded-lg' required>{$answer->explanation}</textarea>";
            echo "</div>";
            echo "</div>";
        }
    
        // Ajouter un champ hidden pour la valeur par défaut de `correct` (0)
        echo "<input type='hidden' name='questions[1][answers][{$index}][correct]' value='0'>";
    
        // Champ checkbox pour marquer la réponse correcte
        echo "<div class='py-2 grid grid-cols-12 w-full'>";
        echo "<label for='correct_answer_1_{$counter}' class='col-span-2 block font-semibold text-gray-700 text-lg'>" . get_string('correct_answer', 'mod_studentqcm') . " ?</label>";
        echo "<div class='col-span-10 w-full flex items-center'>";
        echo "<label class='relative inline-flex items-center cursor-pointer'>";
    
        // On vérifie si la réponse est correcte et on coche la case si c'est le cas
        $checked = ((int)$answer->istrue === 1) ? "checked" : "";
        echo "<input type='checkbox' id='correct_answer_1_{$counter}' name='questions[1][answers][{$index}][correct]' value='1' class='sr-only peer' $checked>";
    
        echo "<span class='w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-lime-400 peer-checked:after:translate-x-full peer-checked:after:bg-white after:content-\"\" after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border after:border-gray-300 after:rounded-full after:h-5 after:w-5 after:transition-all'></span>";
        echo "</label>";
        echo "</div>";
        echo "</div>";
    
        echo "</div>";
    
        $counter++;
    }
    

    // Commentaire / explication globale
    echo "<div class='rounded-3xl bg-indigo-200 my-4 p-4'>";
    echo "<label for='global_comment' class='block font-semibold text-gray-700 text-lg'>" . get_string('global_comment', 'mod_studentqcm') . " :</label>";
    echo "<input type='text' id='global_comment' name='questions[1][global_comment]' class='w-full p-2 mt-2 border border-gray-300 rounded-lg' required value='{$question->global_comment}'>";
    echo "</div>";


echo "</div>";

echo "<div id='hidden_inputs'></div>";

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

document.getElementById('show_new_subcompetency').addEventListener('click', function() {
    var newSubcompetencyField = document.getElementById('new_subcompetency_field');
    // Alterne la visibilité du champ de saisie
    if (newSubcompetencyField.classList.contains('hidden')) {
        newSubcompetencyField.classList.remove('hidden');
    } else {
        newSubcompetencyField.classList.add('hidden');
    }
});

$(document).ready(function() {
    // Activer les champs competency et subcompetency avant la soumission du formulaire
    $('form').on('submit', function() {
        $('#competency_1').prop('disabled', false);
        $('#subcompetency_1').prop('disabled', false);
    });
});



$(document).ready(function() {
    var preselectedSubcompetency = <?= json_encode($question->subcompetency) ?>;
    var questionId = <?= json_encode($question->id) ?>;

    if (preselectedSubcompetency) {
        $.getJSON('fetch_keywords.php', { subcompetency_id: preselectedSubcompetency }, function(allKeywords) {
            $.getJSON('fetch_selected_keywords.php', { question_id: questionId }, function(selectedKeywords) {

                const selectedKeywordIds = Object.values(selectedKeywords);

                var buttons = '';

                // Ajouter les mots-clés sélectionnés dans les inputs cachés
                $('#hidden_inputs').empty();
                $.each(selectedKeywordIds, function(index, keywordId) {
                    $('#hidden_inputs').append(`<input type="hidden" name="questions[1][keywords][]" value="${keywordId}">`);
                });

                $.each(allKeywords, function(index, keyword) {
                    var customIcon = keyword.isCustom ? '<i class="fas fa-pen ml-2"></i>' : '';
                    var isSelected = selectedKeywordIds.includes(keyword.id.toString());
                    var selectedClass = isSelected ? 'bg-indigo-400 text-white' : 'bg-lime-100 text-indigo-400';

                    buttons += `<button type="button" class="keyword-btn px-4 py-2 border border-lime-400 rounded-2xl hover:bg-indigo-400 hover:text-white ${selectedClass}" data-id="${keyword.id}">${keyword.word} ${customIcon}</button>`;
                });

                buttons += `
                    <div class="flex justify-center items-center col-span-1">
                        <button type="button" id="show_new_keyword" class="flex items-center justify-center w-full bg-lime-400 text-white rounded-2xl hover:bg-lime-500 px-4 py-2">
                            <i class="fas fa-plus mr-2"></i>
                            Ajouter un mot clé
                        </button>
                    </div>
                `;

                $('#keywords_list_1').html(buttons);
            });
        });
    }
});


$(document).ready(function() {
    // Filtrer les compétences en fonction du référentiel sélectionné
    $('#referentiel_1').change(function() {
        var referentielId = $(this).val();
        $('#competency_1').html('<option value="">Chargement...</option>').prop('disabled', true);
        $('#subcompetency_1').html('<option value="" disabled selected>Sélectionnez une sous-compétence</option>').prop('disabled', true);

        if (referentielId) {
            $.get('fetch_competencies.php', { referentiel_id: referentielId }, function(data) {
                $('#competency_1').html(data).prop('disabled', false).trigger('change');
            });
        }
    });

    // Mettre à jour les sous-compétences en fonction de la compétence sélectionnée
    $('#competency_1').on('change', function() {
        var competencyId = $(this).val();
        $('#subcompetency_1').html('<option value="" disabled selected>Chargement...</option>').prop('disabled', true);

        // Afficher ou masquer la zone d'ajout de sous-compétence
        if (competencyId) {
            $('#new_subcompetency_container').show();

            $.getJSON('fetch_subcompetencies.php', { competency_id: competencyId }, function(data) {
                if (data.length > 0) {
                    var options = '<option value="" disabled selected>Sélectionnez une sous-compétence</option>';
                    $.each(data, function(index, subcompetency) {
                        var customText = subcompetency.isCustom ? ' (Personnalisée)' : '';
                        options += `<option value="${subcompetency.id}">${subcompetency.name}${customText}</option>`;
                    });
                    $('#subcompetency_1').html(options).prop('disabled', false);
                } else {
                    $('#subcompetency_1').html('<option value="" disabled selected>Aucune sous-compétence disponible</option>').prop('disabled', true);
                }
            }).fail(function() {
                // En cas d'erreur AJAX
                $('#subcompetency_1').html('<option value="" disabled selected>Erreur de chargement</option>').prop('disabled', true);
            });
        } else {
            $('#new_subcompetency_container').hide();
        }
    });

    // Fonction pour ajouter une sous-compétence personnalisée
    $('#add_new_subcompetency').on('click', function() {
        var subcompetencyName = $('#new_subcompetency').val().trim();
        var competencyId = $('#competency_1').val();

        if (subcompetencyName && competencyId) {
            $.post('add_subcompetency.php', 
                { name: subcompetencyName, competency_id: competencyId }, 
                function(response) {
                    if (response.success) {
                        // Ajouter la nouvelle sous-compétence directement dans la liste déroulante
                        $('#subcompetency_1').append(
                            `<option value="${response.id}" class="custom-subcompetency">${response.name} (Personnalisée)</option>`
                        ).prop('disabled', false);

                        // Réinitialiser le champ de texte
                        $('#new_subcompetency').val('');

                        // Afficher un message de confirmation
                        alert('Sous-compétence ajoutée avec succès !');
                    } else {
                        alert('Erreur : ' + response.message);
                    }
                }, 'json'
            ).fail(function() {
                alert("Erreur lors de l'ajout de la sous-compétence.");
            });
        } else {
            alert('Veuillez entrer un nom pour la sous-compétence.');
        }
    });


    // Mise à jour de la liste des mots-clés pour la sous-compétence sélectionnée
    $('#subcompetency_1').change(function() {
        var subcompetencyId = $(this).val();
        $('#keywords_list_1').html('<p class="text-gray-500">Chargement...</p>');

        if (subcompetencyId) {
            $.getJSON('fetch_keywords.php', { subcompetency_id: subcompetencyId }, function(data) {
                var buttons = '';
                
                if (data.length > 0) {
                    $.each(data, function(index, keyword) {

                        var customIcon = keyword.isCustom ? '<i class="fas fa-pen ml-2"></i>' : '';

                        buttons += `
                            <div class="flex justify-center items-center col-span-1">
                                <button type="button" class="keyword-btn px-4 py-2 border border-lime-400 text-indigo-400 rounded-2xl transition-all w-full bg-lime-100 hover:bg-indigo-400 hover:text-white"
                                    data-id="${keyword.id}">
                                    ${keyword.word} ${customIcon}
                                </button>
                            </div>
                        `;
                        
                    });
                } else {
                    buttons = '<p class="text-gray-500">Aucun mot-clé disponible</p>';
                }

                buttons += `
                    <div class="flex justify-center items-center col-span-1">
                        <button type="button" id="show_new_keyword" class="flex items-center justify-center w-full bg-lime-400 text-white rounded-2xl hover:bg-lime-500 px-4 py-2">
                            <i class="fas fa-plus mr-2"></i>
                            Ajouter un mot clé
                        </button>
                    </div>
                `;

                $('#keywords_list_1').html(buttons);
            }).fail(function() {
                $('#keywords_list_1').html('<p class="text-red-500">Erreur de chargement des mots-clés</p>');
            });
        }
    });

    // Gestion du clic sur les boutons pour sélectionner/désélectionner un mot-clé
    $(document).on('click', '.keyword-btn', function() {
        var button = $(this);
        var keywordId = button.data('id');
        var hiddenInput = $('#hidden_inputs input[value="' + keywordId + '"]');

        if (hiddenInput.length > 0) {
            hiddenInput.remove();
            button.removeClass('bg-indigo-400 text-white').addClass('border-lime-400 bg-lime-100 text-indigo-400');
        } else {
            if ($('#hidden_inputs').length === 0) {
                $('#keywords_list_1').append('<div id="hidden_inputs" style="display:none;"></div>');
            }
            $('#hidden_inputs').append(`
                <input type="hidden" name="questions[1][keywords][]" value="${keywordId}">
            `);
            button.removeClass('border-lime-400 bg-lime-100 text-indigo-400').addClass('bg-indigo-400 text-white');
        }
    });

    $(document).on('click', '#show_new_keyword', function() {
        var newKeywordField = $('#new_keyword_field');
        
        if (newKeywordField.hasClass('hidden')) {
            newKeywordField.removeClass('hidden');
        } else {
            newKeywordField.addClass('hidden');
        }
    });

    // Fonction pour ajouter un mot clé personnalisé
    $('#add_new_keyword').on('click', function() {
        var keyword = $('#new_keyword').val().trim();
        var subcompetencyId = $('#subcompetency_1').val();

        if (keyword && subcompetencyId) {
            $.post('add_keyword.php', 
                { word: keyword, subcompetency_id: subcompetencyId }, 
                function(response) {
                    if (response.success) {
                        // Supprimer le bouton "+" avant d'ajouter le mot-clé
                        $('#keywords_list_1').find('#show_new_keyword').parent().remove();

                        var customIcon = '<i class="fas fa-pen ml-2"></i>';

                        var newKeywordButton = `
                            <div class="flex justify-center items-center col-span-1">
                                <button type="button" class="keyword-btn px-4 py-2 border border-lime-400 text-indigo-400 rounded-2xl transition-all w-full bg-lime-100 hover:bg-indigo-400 hover:text-white"
                                    data-id="${response.id}">
                                    ${response.word} ${customIcon}
                                </button>
                            </div>
                        `;

                        $('#keywords_list_1').append(newKeywordButton);

                        $('#keywords_list_1').append(`
                            <div class="flex justify-center items-center col-span-1">
                                <button type="button" id="show_new_keyword" class="flex items-center justify-center w-full bg-lime-400 text-white rounded-2xl hover:bg-lime-500 px-4 py-2">
                                    <i class="fas fa-plus mr-2"></i>
                                    Ajouter un mot clé
                                </button>
                            </div>
                        `);

                        // Réinitialiser le champ de texte
                        $('#new_keyword').val('');

                        // Afficher un message de confirmation
                        alert('Mot-clé ajouté avec succès !');
                    } else {
                        alert('Erreur : ' + response.message);
                    }
                }, 'json'
            ).fail(function(xhr, status, error) {
                alert("Erreur lors de l'ajout du mot-clé : " + xhr.responseText);
            });
        } else {
            alert('Veuillez entrer un mot-clé.');
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

        if ($('#hidden_inputs input').length === 0) {
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
