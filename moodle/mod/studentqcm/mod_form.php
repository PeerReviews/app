<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_studentqcm_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $DB, $PAGE;

        $PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

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
        
        $mform->addElement('html', '<div id="validated_competences-container" class="mt-4"></div>');
        
        $mform->addElement('button', 'add_competences', get_string('add_competences', 'mod_studentqcm'), array('type' => 'button', 'onclick' => 'addCompetenceField()'));
        
        $mform->addElement('html', '<div id="add_competences-container"></div>');

        $mform->addElement('html', '<script>
            let compteur_competence = 0;
            let compteur_subcompetence = 0;
            let compteur_keyword = 0;
            let competencesData = [];   

            function addCompetenceField() {
                let container = document.getElementById("add_competences-container");
                compteur_competence += 1;

                let index_competence = compteur_competence;

                let fieldHTML = `
                    <div id="competence-container${index_competence}" class="competence-block p-4 border border-gray-300 rounded-lg mt-4">
                        <h3 class="text-2xl font-bold">' . get_string('competences_title', 'mod_studentqcm') . '</h3>
                        <label>' . get_string('name_competence', 'mod_studentqcm') . '</label>
                        <input type="text" id="competence-name${compteur_competence}" name="name_competence[]" class="form-control p-2 border rounded w-full" required>

                        <button type="button" class="bg-gray-200 font-bold py-2 px-4 rounded" onclick="addSubCompetenceField(${index_competence})">
                            ' . get_string('add_subcompetences', 'mod_studentqcm') . '
                        </button>

                        <div id="add_subcompetences-container${index_competence}" class="mt-4"></div>

                        <button type="button" class="bg-green-500 text-white font-bold py-2 px-4 rounded mt-4" onclick="validateCompetence(${index_competence})">
                            ' . get_string('validate', 'mod_studentqcm') . '
                        </button>
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
                        <input type="text" id="subcompetence-name${index_competence}${compteur_subcompetence}" name="name_subcompetence[]" class="form-control p-2 border rounded w-full" required>

                        <button type="button" class="bg-gray-200 font-bold py-2 px-4 rounded" onclick="addKeyword(${index_competence}, ${compteur_subcompetence})">
                            ' . get_string('add_keyword', 'mod_studentqcm') . '
                        </button>

                        <div id="add_keyword-container${index_competence}${compteur_subcompetence}" class="mt-4"></div>
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
                        <input type="text" id="keyword-name${index_competence}${index_subcompetence}${compteur_keyword}" name="name_keyword[]" class="form-control p-2 border rounded w-full" required>
                    </div>
                `;

                container.insertAdjacentHTML("beforeend", fieldHTML);
            }

            function validateCompetence(index_competence) {
                let competenceName = document.getElementById(`competence-name${index_competence}`).value.trim();
                if (!competenceName) {
                    alert("Veuillez entrer un nom de compétence !");
                    return;
                }

                let subCompetences = [];
                document.querySelectorAll(`[id^=subcompetences-container${index_competence}]`).forEach(subCompetenceDiv => {
                    let subCompetenceId = subCompetenceDiv.id.match(/subcompetences-container(\d+)(\d+)/);
                    if (subCompetenceId) {
                        let subCompetenceName = document.getElementById(`subcompetence-name${subCompetenceId[1]}${subCompetenceId[2]}`).value.trim();
                        if (subCompetenceName) {
                            let keywords = [];
                            document.querySelectorAll(`[id^=keyword-container${subCompetenceId[1]}${subCompetenceId[2]}] input`).forEach(keywordInput => {
                                if (keywordInput.value.trim()) {
                                    keywords.push(keywordInput.value.trim());
                                } else {
                                    alert("Veuillez entrer un nom de sous-compétence !");
                                    return;
                                }
                            });

                            subCompetences.push({
                                name: subCompetenceName,
                                keywords: keywords
                            });
                        } else {
                            alert("Veuillez entrer un nom de sous-compétence !");
                            return;
                        }
                    }
                });

                let competenceData = {
                    name: competenceName,
                    subCompetences: subCompetences
                };

                competencesData.push(competenceData);

                // Supprimer la section et afficher les données sous forme de texte
                document.getElementById(`competence-container${index_competence}`).remove();
                displayValidatedCompetences();
            }

            function displayValidatedCompetences() {
                let validatedContainer = document.getElementById("validated_competences-container");
                validatedContainer.innerHTML = "";

                competencesData.forEach((competence, index) => {
                    let html = `
                        <div class="p-4 border border-green-500 rounded-lg mt-4 bg-green-100">
                            <h3 class="text-2xl font-bold text-green-700">${competence.name}</h3>
                            <ul class="mt-2">
                                ${competence.subCompetences.map(sub => `
                                    <li class="ml-4">
                                        <span class="font-bold">${sub.name}</span>
                                        <ul class="ml-6">
                                            ${sub.keywords.map(keyword => `<li class="text-sm text-gray-600">${keyword}</li>`).join("")}
                                        </ul>
                                    </li>
                                `).join("")}
                            </ul>
                        </div>
                    `;
                    validatedContainer.insertAdjacentHTML("beforeend", html);
                });
            }

        </script>');

        $mform->addElement('html', '<h2 class="text-3xl font-bold">' . get_string('Choix des cours à ajouter', 'mod_studentqcm') . '</h2>');

        $mform->addElement('html', '
            <div id="drop-area-courses" class="drop-area">
                <p>Déposez la liste des étudiants ici ou cliquez pour en sélectionner.</p>
                <input type="file" id="fileInputEtu" multiple hidden>
            </div>
            <div id="file-list"></div>

            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const dropArea = document.getElementById("drop-area-prof");
                    const fileInput = document.getElementById("fileInput");
                    const fileList = document.getElementById("file-list");
                    let uploadedFiles = [];

                    dropArea.addEventListener("click", () => fileInput.click());

                    fileInput.addEventListener("change", function (event) {
                        handleFiles(event.target.files);
                    });

                    dropArea.addEventListener("dragover", (event) => {
                        event.preventDefault();
                        dropArea.classList.add("drag-over");
                    });

                    dropArea.addEventListener("dragleave", () => {
                        dropArea.classList.remove("drag-over");
                    });

                    dropArea.addEventListener("drop", (event) => {
                        event.preventDefault();
                        dropArea.classList.remove("drag-over");
                        handleFiles(event.dataTransfer.files);
                    });

                    function handleFiles(files) {
                        for (let file of files) {
                            uploadedFiles.push(file);
                            let listItem = document.createElement("div");
                            listItem.className = "file-item";
                            listItem.textContent = file.name;
                            fileList.appendChild(listItem);
                        }

                        // Mise à jour du champ caché pour stocker les noms des fichiers
                        document.getElementById("id_uploaded_files").value = uploadedFiles.map(f => f.name).join(",");
                    }
                });
            </script>
        ');

        $mform->addElement('filemanager', 'filemanager', get_string('uploadfile', 'mod_studentqcm'), null, [
            'maxbytes' => 10485760, // 10MB
            'subdirs' => 0,
            'maxfiles' => 10, // Autoriser jusqu'à 10 fichiers
            'accepted_types' => ['.jpg', '.png', '.pdf', '.docx', '*']
        ]);

        $mform->addElement('hidden', 'uploaded_files_etu', '');
        $mform->setType('uploaded_files_etu', PARAM_RAW);

        $mform->addElement('html', '</div>');

        // $mform->addElement('date_selector', 'date_field', get_string('date', 'mod_studentqcm'));

        // $mform->addElement('html', '
        //     <label for="date-slider">Choisissez une date :</label>
        //     <input type="range" id="date-slider" min="1704067200" max="1735689600" step="86400">
        //     <input type="text" id="date-display" readonly>
        //     <script>
        //         document.addEventListener("DOMContentLoaded", function() {
        //             const slider = document.getElementById("date-slider");
        //             const display = document.getElementById("date-display");

        //             function formatDate(timestamp) {
        //                 const date = new Date(timestamp * 1000);
        //                 return date.toISOString().split("T")[0]; // Format YYYY-MM-DD
        //             }

        //             slider.addEventListener("input", function() {
        //                 display.value = formatDate(slider.value);
        //             });

        //             display.value = formatDate(slider.value);
        //         });
        //     </script>
        // ');

      
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

        // Choix étudiants tiers-temps
        $mform->addElement('html', '<div class="m-8 rounded-2xl p-4  bg-gray-200">');

        $mform->addElement('html', '<h2 class="text-3xl font-bold">' . get_string('choice_etu_tt_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('static', 'import_etu', get_string('import_etu', 'mod_studentqcm'), null);
        
        $mform->addElement('html', '
            <div id="drop-area-etu" class="drop-area">
                <p>Déposez la liste des étudiants ici ou cliquez pour en sélectionner.</p>
                <input type="file" id="fileInputEtu" multiple hidden>
            </div>
            <div id="file-list-etu"></div>
        ');

        $mform->addElement('hidden', 'uploaded_files_etu', '');
        $mform->setType('uploaded_files_etu', PARAM_RAW);

        // Ajout du champ de sélection de fichiers Moodle (optionnel)
        $mform->addElement('filepicker', 'file', get_string('uploadfile', 'mod_studentqcm'), null, [
            'maxbytes' => 10485760, // 10MB
            'accepted_types' => 'csv'
        ]);

        $mform->addElement('button', 'add_etu', get_string('add_etu', 'mod_studentqcm'), array('type' => 'button', 'onclick' => 'addEtuField()'));
        
        $mform->addElement('html', '<div id="validate_etu-container" class="p-4 border border-gray-300 rounded-lg mt-4 bg-white">');
        $mform->addElement('html', '<h3 class="text-2xl font-bold text-green-700">Etudiants sélectionnés</h3>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div id="add_etu-container"></div>');

        $mform->addElement('html', '<script>

            let infoEtus = []
            function addEtuField() {
                let etuContainer = document.getElementById("add_etu-container");
                etuContainer.innerHTML = "";

                let html = `
                    <div id="info_etu-container" class="p-4 border border-gray-300 rounded-lg mt-4 bg-white">
                        <h3 class="text-2xl font-bold text-green-700">' . get_string('info_etu', 'mod_studentqcm') . '</h3>
                        <label>' . get_string('surname', 'mod_studentqcm') . '</label>
                        <input type="text" id="surname_etu" class="form-control p-2 border rounded w-full" required>

                        <label>' . get_string('name', 'mod_studentqcm') . '</label>
                        <input type="text" id="name_etu" class="form-control p-2 border rounded w-full" required>

                        <label>' . get_string('mail', 'mod_studentqcm') . '</label>
                        <input type="text" id="mail_etu" class="form-control p-2 border rounded w-full" required>

                        <button type="button" class="bg-gray-200 font-bold py-2 px-4 rounded" onclick="validateEtu()">
                            ' . get_string('validate', 'mod_studentqcm') . '
                        </button>
                    </div>
                `;
                etuContainer.insertAdjacentHTML("beforeend", html);
            }

            function validateEtu() {
                let etuName = document.getElementById("name_etu").value.trim();
                let etuSurname = document.getElementById("surname_etu").value.trim();
                let etuMail = document.getElementById("mail_etu").value.trim();

                if (!etuName) {
                    alert("Veuillez entrer un nom d\'étudiant !");
                    return;
                }

                if (!etuSurname) {
                    alert("Veuillez entrer un prénom d\'étudiant !");
                    return;
                }

                if (!etuMail) {
                    alert("Veuillez entrer un mail d\'étudiant !");
                    return;
                }

                let infoEtu = [];
                infoEtu.push({
                    name: etuName,
                    surname: etuSurname,
                    mail: etuMail
                });

                infoEtus.push(infoEtu);

                document.getElementById("info_etu-container").remove();
                displayValidatedEtu(infoEtu);
            }

            function displayValidatedEtu(infoEtu) {
                let validatedContainer = document.getElementById("validate_etu-container");
                console.log("infoEtu: ", infoEtu);
                let html = `
                    <div class="p-4-lg mt-4">
                        <p class="text-2xl">${infoEtu[0].name} ${infoEtu[0].surname} ${infoEtu[0].mail}</p>
                    </div>
                    `;
                    validatedContainer.insertAdjacentHTML("beforeend", html);
            }

        </script>');


        //Ajout bouton "Valider"
        $mform->addElement('html', '</div>');

        // Choix prof
        $mform->addElement('html', '<div class="m-8 rounded-2xl p-4  bg-gray-200">');

        $mform->addElement('html', '<h2 class="text-3xl font-bold">' . get_string('choice_prof_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('html', '
            <div id="drop-area-prof" class="drop-area">
                <p>Déposez vos fichiers ici ou cliquez pour en sélectionner.</p>
                <input type="file" id="fileInput" multiple hidden>
            </div>
            <div id="file-list"></div>
        ');

        // Ajout du champ caché pour stocker les fichiers sélectionnés
        $mform->addElement('hidden', 'uploaded_files', '');
        $mform->setType('uploaded_files', PARAM_RAW);

        // Ajout du champ de sélection de fichiers Moodle (optionnel)
        $mform->addElement('filepicker', 'file', get_string('uploadfile', 'mod_studentqcm'), null, [
            'maxbytes' => 10485760, // 10MB
            'accepted_types' => '*'
        ]);

        $mform->addElement('html', '<script>
            document.addEventListener("DOMContentLoaded", function () {
                const dropArea = document.getElementById("drop-area");
                const fileInput = document.getElementById("fileInput");
                const fileList = document.getElementById("file-list");
                const uploadedFilesField = document.querySelector("input[name="uploaded_files"]");

                dropArea.addEventListener("dragover", function (e) {
                    e.preventDefault();
                    dropArea.style.backgroundColor = "#e9ecef";
                });

                dropArea.addEventListener("dragleave", function () {
                    dropArea.style.backgroundColor = "#f8f9fa";
                });

                dropArea.addEventListener("drop", function (e) {
                    e.preventDefault();
                    dropArea.style.backgroundColor = "#f8f9fa";

                    const files = e.dataTransfer.files;
                    handleFiles(files);
                });

                dropArea.addEventListener("click", function () {
                    fileInput.click();
                });

                fileInput.addEventListener("change", function (e) {
                    handleFiles(fileInput.files);
                });

                function handleFiles(files) {
                    let fileNames = [];

                    for (let file of files) {
                        let fileItem = document.createElement("div");
                        fileItem.classList.add("file-item");
                        fileItem.textContent = file.name;
                        fileList.appendChild(fileItem);
                        fileNames.push(file.name);
                    }

                    // Stocker les fichiers sélectionnés dans un champ caché
                    uploadedFilesField.value = fileNames.join(",");
                }
            });

        </script>
        
        ');

       
        $mform->addElement('button', 'add_prof', get_string('add_prof', 'mod_studentqcm'), array('type' => 'button', 'onclick' => 'addProfField()'));
        
        $mform->addElement('html', '<div id="validate_prof-container" class="p-4 border border-gray-300 rounded-lg mt-4 bg-white">');
        $mform->addElement('html', '<h3 class="text-2xl font-bold text-green-700">Professeurs sélectionnés</h3>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div id="add_prof-container"></div>');

        $mform->addElement('html', '<script>
            let infoProfs = []
            function addProfField() {
                let profContainer = document.getElementById("add_prof-container");
                profContainer.innerHTML = "";

                let html = `
                    <div id="info_prof-container" class="p-4 border border-gray-300 rounded-lg mt-4 bg-white">
                        <h3 class="text-2xl font-bold text-green-700">' . get_string('info_prof', 'mod_studentqcm') . '</h3>
                        <label>' . get_string('surname', 'mod_studentqcm') . '</label>
                        <input type="text" id="surname_prof" class="form-control p-2 border rounded w-full" required>

                        <label>' . get_string('name', 'mod_studentqcm') . '</label>
                        <input type="text" id="name_prof" class="form-control p-2 border rounded w-full" required>

                        <label>' . get_string('mail', 'mod_studentqcm') . '</label>
                        <input type="text" id="mail_prof" class="form-control p-2 border rounded w-full" required>

                        <button type="button" class="bg-gray-200 font-bold py-2 px-4 rounded" onclick="validateProf()">
                            ' . get_string('validate', 'mod_studentqcm') . '
                        </button>
                    </div>
                `;
                profContainer.insertAdjacentHTML("beforeend", html);
            }

            function validateProf() {
                let profName = document.getElementById("name_prof").value.trim();
                let profSurname = document.getElementById("surname_prof").value.trim();
                let profMail = document.getElementById("mail_prof").value.trim();

                if (!profName) {
                    alert("Veuillez entrer un nom de professeur !");
                    return;
                }

                if (!profSurname) {
                    alert("Veuillez entrer un prénom de professeur !");
                    return;
                }

                if (!profMail) {
                    alert("Veuillez entrer un mail de professeur !");
                    return;
                }

                let infoProf = [];
                infoProf.push({
                    name: profName,
                    surname: profSurname,
                    mail: profMail
                });

                infoProfs.push(infoProf);

                document.getElementById("info_prof-container").remove();
                displayValidatedProf(infoProf);
            }

            function displayValidatedProf(infoProf) {
                let validatedContainer = document.getElementById("validate_prof-container");
                let html = `
                    <div class="p-4-lg mt-4">
                        <p class="text-2xl">${infoProf[0].name} ${infoProf[0].surname} ${infoProf[0].mail}</p>
                    </div>
                    `;
                    validatedContainer.insertAdjacentHTML("beforeend", html);
            }

        </script>');
        
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


                function getPopQuestionsCount() {
                    let totalPopQuestions = 0;
                    document.querySelectorAll("#popOption_qcm_qcu-container select").forEach(select => {
                        totalPopQuestions += parseInt(select.value) || 0;
                    });
                    return totalPopQuestions;
                }

                function displayPOP() {
                    let pop = parseInt(popSelect.value) || 0;
                    let popContainer = document.getElementById("popOption_qcm_qcu-container");
                    popContainer.innerHTML = "";

                    for (let i = 0; i < pop; i++) {
                        let fieldHTML = `
                            <div class="pop-block p-4 border border-gray-300 rounded-lg mt-4 bg-white">
                                <h3 class="text-xl font-bold">POP ${i + 1}</h3>
                                <label>' . get_string('nb_qcm', 'mod_studentqcm') . '</label>
                                <select name="pop_qcm${i}[]" class="form-control p-2 border rounded w-full" required>
                                    ${generateOptionPOPQCM(i)}
                                </select>
                                <label>' . get_string('nb_qcu', 'mod_studentqcm') . '</label>
                                <select name="pop_qcu${i}[]" class="form-control p-2 border rounded w-full" required>
                                    ${generateOptionPOPQCU(i)}
                                </select>
                            </div>
                        `;

                        popContainer.insertAdjacentHTML("beforeend", fieldHTML);
                    }

                    document.querySelectorAll("#popOption_qcm_qcu-container select").forEach(select => {
                        select.addEventListener("change", updateOptions);
                    });

                    updateOptions();
                }

                function generateOptionPOPQCM(index_pop) {
                    let qcm = parseInt(qcmSelect.value) || 0;
                    let qcu = parseInt(qcuSelect.value) || 0;
                    let tcs = parseInt(tcsSelect.value) || 0;
                    
                    let totalPopQuestions = getPopQuestionsCount();

                    const POPqcmSelect = document.querySelector(`select[name="pop_qcm${index_pop}[]"]`);
                    let POPqcm = parseInt(POPqcmSelect?.value) || 0;

                    let totalUsed = qcm + qcu + tcs + totalPopQuestions - POPqcm;
                    let remaining = 18 - totalUsed;

                    let optionsPop = "";
                    for (let i = 0; i <= remaining; i++) {
                        optionsPop += `<option value="${i}">${i}</option>`;
                    }

                    return optionsPop;
                }

                function generateOptionPOPQCU(index_pop) {
                    let qcm = parseInt(qcmSelect.value) || 0;
                    let qcu = parseInt(qcuSelect.value) || 0;
                    let tcs = parseInt(tcsSelect.value) || 0;
                    let totalPopQuestions = getPopQuestionsCount();

                    const POPqcuSelect = document.querySelector(`select[name="pop_qcu${index_pop}[]"]`);
                    let POPqcu = parseInt(POPqcuSelect?.value) || 0;

                    let totalUsed = qcm + qcu + tcs + totalPopQuestions - POPqcu;
                    let remaining = 18 - totalUsed;

                    let optionsPop = "";
                    for (let i = 0; i <= remaining; i++) {
                        optionsPop += `<option value="${i}">${i}</option>`;
                    }

                    return optionsPop;
                }

                function updateOptions() {
                    let qcm = parseInt(qcmSelect.value) || 0;
                    let qcu = parseInt(qcuSelect.value) || 0;
                    let tcs = parseInt(tcsSelect.value) || 0;
                    let totalPopQuestions = getPopQuestionsCount();

                    let totalUsed = qcm + qcu + tcs + totalPopQuestions;
                    let remaining = 18 - totalUsed; // Questions restantes disponibles

                    console.log("totalUsed: ", totalUsed);

                    selects.forEach(select => {
                        let currentValue = parseInt(select.value) || 0;
                        select.innerHTML = ""; // Réinitialiser les options
                        for (let i = 0; i <= remaining + currentValue; i++) {
                            let option = document.createElement("option");
                            option.value = i;
                            option.textContent = i;
                            select.appendChild(option);
                        }
                        
                        select.value = currentValue;
                    });

                    document.querySelectorAll("#popOption_qcm_qcu-container select").forEach(select => {
                        let currentValue = parseInt(select.value) || 0;

                        let match = select.name.match(/\d+/);  // Cherche un nombre dans le name
                        let indexPOP = match ? match[0] : null;
                        if (select.name.includes("qcm")) {
                            select.innerHTML = generateOptionPOPQCM(indexPOP);
                        } else {
                            select.innerHTML = generateOptionPOPQCU(indexPOP);
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