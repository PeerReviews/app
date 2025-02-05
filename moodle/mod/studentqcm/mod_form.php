<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_studentqcm_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $DB, $PAGE;

        $PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));
        // $PAGE->requires->js(new moodle_url('/mod/studentqcm/js/mod_form.js'));

        $mform = $this->_form;

        // Informations principales du référentiel.
        $mform->addElement('html', '<div class="mb-8 rounded-2xl p-4  bg-gray-200">');

        $mform->addElement('html', '<h2 class="text-3xl font-bold">' . get_string('info_referentiel_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('text', 'name_referentiel', get_string('name_referentiel', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('name_referentiel', PARAM_TEXT);
        $mform->addRule('name_referentiel', null, 'required', null, 'client');
        
        $mform->addElement('editor', 'intro', get_string('intro', 'mod_studentqcm'), null);
        $mform->setType('intro', PARAM_RAW);
        $mform->addRule('intro', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'date_start_referentiel', get_string('date_start_referentiel', 'mod_studentqcm'));
        $mform->addRule('date_start_referentiel', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'date_end_referentiel', get_string('date_end_referentiel', 'mod_studentqcm'));
        $mform->addRule('date_end_referentiel', null, 'required', null, 'client');

        // Ajout compétences
        $mform->addElement('html', '<h2 class="text-3xl font-bold">' . get_string('competences_title', 'mod_studentqcm') . '</h2>');
        
        $mform->addElement('button', 'add_competences', get_string('add_competences', 'mod_studentqcm'), array('type' => 'button', 'onclick' => 'addCompetenceField()'));
        $mform->addElement('html', '<div id="add_competences-container"></div>');

        $mform->addElement('html', '<script>
            let compteur_competence = 0;
            let compteur_subcompetence = 0;
            let compteur_keyword = 0;

            function addCompetenceField() {
                let container = document.getElementById("add_competences-container");
                compteur_competence += 1;

                let fieldHTML = `
                    <div id="competence-container${compteur_competence}" class="competence-block p-4 border border-gray-300 rounded-lg mt-4">
                        <h3 class="text-2xl font-bold">' . get_string('competences_title', 'mod_studentqcm') . '</h3>
                        <label>' . get_string('name_competence', 'mod_studentqcm') . '</label>
                        <input type="text" name="name_competence[]" class="form-control p-2 border rounded w-full" required>

                        <button type="button" class="bg-gray-200 font-bold py-2 px-4 rounded" onclick="addSubCompetenceField(${compteur_competence})">
                            ' . get_string('add_subcompetences', 'mod_studentqcm') . '
                        </button>

                        <div id="add_subcompetences-container${compteur_competence}" class="mt-4"></div>
                    </div>
                `;

                container.insertAdjacentHTML("beforeend", fieldHTML);
            }

            function addSubCompetenceField(index_competence) {
                let container = document.getElementById(`add_subcompetences-container${index_competence}`);
                compteur_subcompetence += 1;

                let fieldHTML = `
                    <div id="subcompetences-container${index_competence}${compteur_subcompetence}" class="subcompetence-block p-4 border border-gray-300 rounded-lg mt-4">
                        <h3 class="text-2xl font-bold">' . get_string('subcompetences_title', 'mod_studentqcm') . '</h3>
                        <label>' . get_string('name_subcompetence', 'mod_studentqcm') . '</label>
                        <input type="text" name="name_subcompetence[]" class="form-control p-2 border rounded w-full" required>

                        <button type="button" class="bg-gray-200 font-bold py-2 px-4 rounded" onclick="addKeyword(${index_competence}, ${compteur_subcompetence})">
                            ' . get_string('add_keyword', 'mod_studentqcm') . '
                        </button>

                        <div id="add_keyword-container${index_competence}${compteur_subcompetence}" class="mt-4"></div>
                        <button type="button" class="bg-gray-200 font-bold py-2 px-4 rounded" >
                            ' . get_string('validate', 'mod_studentqcm') . '
                        </button>
                    </div>
                `;

                container.insertAdjacentHTML("beforeend", fieldHTML);
            }

            function addKeyword(index_competence, index_subcompetence) {
                let container = document.getElementById(`add_keyword-container${index_competence}${index_subcompetence}`);
                compteur_keyword += 1;

                let fieldHTML = `
                    <div id="keyword-container${index_competence}${index_subcompetence}${compteur_keyword}" class="keyword-block p-4 border border-gray-300 rounded-lg mt-4">
                        <h3 class="text-2xl font-bold">' . get_string('keyword_title', 'mod_studentqcm') . '</h3>
                        <label>' . get_string('name_keyword', 'mod_studentqcm') . '</label>
                        <input type="text" name="name_keyword[]" class="form-control p-2 border rounded w-full" required>
                    </div>
                `;

                container.insertAdjacentHTML("beforeend", fieldHTML);
            }
        </script>');

        $mform->addElement('html', '</div>');


        // Ajout des champs de date.
        $mform->addElement('html', '<div class="m-8 rounded-2xl p-4  bg-gray-200">');

        $mform->addElement('html', '<h2 class="text-3xl font-bold">' . get_string('phases_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('date_selector', 'start_date_1', get_string('start_date_1', 'mod_studentqcm'));
        $mform->addRule('start_date_1', null, 'required', null, 'client');
        
        $mform->addElement('date_selector', 'end_date_1', get_string('end_date_1', 'mod_studentqcm'));
        $mform->addRule('end_date_1', null, 'required', null, 'client');
        
        $mform->addElement('date_selector', 'end_date_tt_1', get_string('end_date_tt_1', 'mod_studentqcm'));
        $mform->addRule('end_date_tt_1', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'start_date_2', get_string('start_date_2', 'mod_studentqcm'));
        $mform->addRule('start_date_2', null, 'required', null, 'client');
        
        $mform->addElement('date_selector', 'end_date_2', get_string('end_date_2', 'mod_studentqcm'));
        $mform->addRule('end_date_2', null, 'required', null, 'client');
        
        $mform->addElement('date_selector', 'end_date_tt_2', get_string('end_date_tt_2', 'mod_studentqcm'));
        $mform->addRule('end_date_tt_2', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'start_date_3', get_string('start_date_3', 'mod_studentqcm'));
        $mform->addRule('start_date_3', null, 'required', null, 'client');
        
        $mform->addElement('date_selector', 'end_date_3', get_string('end_date_3', 'mod_studentqcm'));
        $mform->addRule('end_date_3', null, 'required', null, 'client');
        
        $mform->addElement('date_selector', 'end_date_tt_3', get_string('end_date_tt_3', 'mod_studentqcm'));
        $mform->addRule('end_date_tt_3', null, 'required', null, 'client');

        $mform->addElement('html', '</div>');

        // Choix étudiants
        $mform->addElement('html', '<div class="m-8 rounded-2xl p-4  bg-gray-200">');

        $mform->addElement('html', '<h2 class="text-3xl font-bold">' . get_string('choice_etu_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('static', 'import_etu', get_string('import_etu', 'mod_studentqcm'), null);

        $mform->addElement('static', 'selected_etu', get_string('selected_etu', 'mod_studentqcm'), null);
        // Ajout du bouton "Ajouter un étudiant"
        $mform->addElement('html', '<h3 class="text-2xl font-bold">' . get_string('info_etu', 'mod_studentqcm') . '</h3>');

        $mform->addElement('text', 'surname', get_string('surname', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('surname', PARAM_TEXT);
        $mform->addRule('surname', null, 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('name', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'mail', get_string('mail', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('mail', PARAM_TEXT);
        $mform->addRule('mail', null, 'required', null, 'client');
        //Ajout bouton "Valider"
        $mform->addElement('html', '</div>');

        // Choix étudiants tiers-temps
        $mform->addElement('html', '<div class="m-8 rounded-2xl p-4  bg-gray-200">');

        $mform->addElement('html', '<h2 class="text-3xl font-bold">' . get_string('choice_etu_tt_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('static', 'import_etu', get_string('import_etu', 'mod_studentqcm'), null);

        $mform->addElement('static', 'selected_etu', get_string('selected_etu', 'mod_studentqcm'), null);
        // Ajout du bouton "Ajouter un étudiant"
        $mform->addElement('html', '<h3 class="text-2xl font-bold">' . get_string('info_etu', 'mod_studentqcm') . '</h3>');

        $mform->addElement('text', 'surname', get_string('surname', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('surname', PARAM_TEXT);
        $mform->addRule('surname', null, 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('name', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'mail', get_string('mail', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('mail', PARAM_TEXT);
        $mform->addRule('mail', null, 'required', null, 'client');
        //Ajout bouton "Valider"
        $mform->addElement('html', '</div>');

        // Choix prof
        $mform->addElement('html', '<div class="m-8 rounded-2xl p-4  bg-gray-200">');

        $mform->addElement('html', '<h2 class="text-3xl font-bold">' . get_string('choice_prof_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('static', 'import_prof', get_string('import_prof', 'mod_studentqcm'), null);

        $mform->addElement('static', 'selected_prof', get_string('selected_prof', 'mod_studentqcm'), null);
        // Ajout du bouton "Ajouter un étudiant"
        $mform->addElement('html', '<h3 class="text-2xl font-bold">' . get_string('info_prof', 'mod_studentqcm') . '</h3>');

        $mform->addElement('text', 'surname', get_string('surname', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('surname', PARAM_TEXT);
        $mform->addRule('surname', null, 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('name', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'mail', get_string('mail', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('mail', PARAM_TEXT);
        $mform->addRule('mail', null, 'required', null, 'client');
        //Ajout bouton "Valider"

        $mform->addElement('html', '</div>');


        // Choix types éval
        $mform->addElement('html', '<div class="m-8 rounded-2xl p-4 bg-gray-200">');

        $options = ['0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17', '18' => '18'];

        $mform->addElement('html', '<h2 class="text-3xl font-bold">' . get_string('type_eval_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('select', 'choix_qcm', get_string('nb_qcm', 'mod_studentqcm'), $options);
        $mform->addElement('select', 'choix_qcu', get_string('nb_qcu', 'mod_studentqcm'), $options);
        $mform->addElement('select', 'choix_tcs', get_string('nb_tcs', 'mod_studentqcm'), $options);
        $mform->addElement('select', 'choix_pop', get_string('nb_pop', 'mod_studentqcm'), $options);

        $mform->addElement('html', '<div id="popOption_qcm_qcu-container"></div>');

        // Ajout du script JavaScript
        $mform->addElement('html', '
         <script>
        
            document.addEventListener("DOMContentLoaded", function () {
                const qcmSelect = document.querySelector("select[name=\'choix_qcm\']");
                const qcuSelect = document.querySelector("select[name=\'choix_qcu\']");
                const tcsSelect = document.querySelector("select[name=\'choix_tcs\']");
                const popSelect = document.querySelector("select[name=\'choix_pop\']");
                const selects = [qcmSelect, qcuSelect, tcsSelect, popSelect];

                function displayPOP() {
                    let pop = parseInt(popSelect.value) || 0;
                    let popContainer = document.getElementById("popOption_qcm_qcu-container");
                    popContainer.innerHTML = "";
                    console.log("Dans displayPOP: ");

                    for (let i = 0; i < pop; i++) {
                        let fieldHTML = `
                            <div class="pop-block p-4 border border-gray-300 rounded-lg mt-4 bg-white">
                                <h3 class="text-xl font-bold">POP ${i + 1}</h3>
                                <label>' . get_string('nb_qcm', 'mod_studentqcm') . '</label>
                                <select name="pop_type[]" class="form-control p-2 border rounded w-full" required>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                                <label>' . get_string('nb_qcu', 'mod_studentqcm') . '</label>
                                <select name="pop_type[]" class="form-control p-2 border rounded w-full" required>
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                </select>
                            </div>
                        `;

                        popContainer.insertAdjacentHTML("beforeend", fieldHTML);
                    }
                }

                 function updateOptions() {
                    let qcm = parseInt(qcmSelect.value) || 0;
                    let qcu = parseInt(qcuSelect.value) || 0;
                    let tcs = parseInt(tcsSelect.value) || 0;
                    

                    let totalUsed = qcm + qcu + tcs;
                    let remaining = 18 - totalUsed; // Questions restantes disponibles

                    selects.forEach(select => {
                        let currentValue = parseInt(select.value) || 0;
                        select.innerHTML = ""; // Réinitialiser les options

                        for (let i = 0; i <= remaining + currentValue; i++) {
                            let option = document.createElement("option");
                            option.value = i;
                            option.textContent = i;
                            select.appendChild(option);
                        }

                        select.value = currentValue > remaining ? remaining : currentValue;
                    });
                }

                selects.forEach(select => {
                    select.addEventListener("change", updateOptions);
                });
                
                updateOptions();

                popSelect.addEventListener("change", displayPOP);

                displayPOP();

            });
        </script>');

        $mform->addElement('html', '</div>');


        $mform->addElement('html', '<div class="m-8 rounded-2xl p-4  bg-gray-200">');
        
        $this->standard_coursemodule_elements();

        // Ajouter les boutons de soumission.
        $this->add_action_buttons();
        
        $mform->addElement('html', '</div>');
    }
}