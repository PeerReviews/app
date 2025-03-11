<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_studentqcm_mod_form extends moodleform_mod
{

    public function definition()
    {
        global $CFG, $DB, $PAGE;

        $PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

        $mform = $this->_form;

        // Informations principales du référentiel.
        $mform->addElement('html', '<div class="mb-8 rounded-2xl p-4 bg-sky-100">');

        $mform->addElement('html', '<h2 class="mb-8 text-3xl font-bold">' . get_string('info_referentiel_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('text', 'name_plugin', get_string('name_plugin', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('name_plugin', PARAM_TEXT);
        $mform->addRule('name_plugin', null, 'required', null, 'client');

        $mform->addElement('text', 'name_referentiel', get_string('name_referentiel', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('name_referentiel', PARAM_TEXT);
        $mform->addRule('name_referentiel', null, 'required', null, 'client');

        $mform->addElement('editor', 'intro', get_string('intro', 'mod_studentqcm'), null);
        $mform->setType('intro', PARAM_RAW);

        $mform->addElement('date_selector', 'date_start_referentiel', get_string('date_start_referentiel', 'mod_studentqcm'));
        $mform->addRule('date_start_referentiel', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'date_end_referentiel', get_string('date_end_referentiel', 'mod_studentqcm'));
        $mform->addRule('date_end_referentiel', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'date_jury', get_string('date_jury', 'mod_studentqcm'));
        $mform->addRule('date_jury', null, 'required', null, 'client');

        // Ajout compétences
        $mform->addElement('html', '<h2 class="text-3xl font-bold">' . get_string('competences_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement(
            'html', '
            <div id="error_competence"></div>
            <div class="mt-4">
                <p>' . get_string('upload_competence', 'mod_studentqcm') . '</p>
            </div>
            <div class="flex justify-center items-center">
                <button type="button" id="choice_add_competence_manual" onclick="manualAddCompetenceField()" class="bg-white p-2 m-4 rounded font-bold hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md">'
            . get_string('add_manual_competence', 'mod_studentqcm') .
            '</button>
                <button type="button" id="choice_add_competence_files" onclick="filesAddCompetenceField()" class="bg-white p-2 m-4 rounded font-bold hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md">'
            . get_string('add_files_competence', 'mod_studentqcm') .
            '</button>
            </div>

            <div id="choice_add_competence"></div>

            <div id="info-json" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-900 bg-opacity-50">
                <div class="bg-white rounded-3xl py-4 px-16 max-w-xl w-full">
                    <div class="flex flex-row-reverse">
                        <button type="button" id="close-info-json" class="text-gray-600 hover:text-gray-800 font-bold text-xl">&times;</button>
                    </div>
                    <p class="font-semibold text-lg mb-2">Format attendu :</p>
                    <pre class="bg-gray-100 p-4 rounded-lg text-sm overflow-x-auto">
[
    {
        "id": 0,
        "name": "Compétence 1",
        "subCompetences": [
            {
                "name": "Sous-compétence 1.1",
                "keywords": [
                    "Mot-cle 1.1.1"
                ]
            },
            {
                "name": "Sous-compétence 1.2",
                "keywords": []
            }
        ]
    },
    {
        "id": 1,
        "name": "Compétence 2",
        "subCompetences": [
            {
                "name": "Sous-compétence 2.1",
                "keywords": [
                    "Mot-cle 2.1.1",
                    "Mot-cle 2.1.2"
                ]
            }
        ]
    }
]
                    </pre>
                </div>
            </div>


            <script>

                function showInfoJSON() {
                    document.getElementById("info-json").classList.remove("hidden");
                }
                
                document.getElementById("close-info-json").addEventListener("click", function () {
                    document.getElementById("info-json").classList.add("hidden");
                });

                document.addEventListener("DOMContentLoaded", function () {
                    const manual = document.getElementById("choice_add_competence_manual");
                    const files  = document.getElementById("choice_add_competence_files");

                    function resetButtons() {
                        manual.classList.remove("bg-gray-200");
                        manual.classList.remove("text-gray-700");
                        manual.classList.add("bg-white");
                        manual.classList.add("text-black");
                        files.classList.remove("bg-gray-200");
                        files.classList.remove("text-gray-700");
                        files.classList.add("bg-white");
                        files.classList.add("text-black");
                    }

                    manual.addEventListener("click", function () {
                        resetButtons();
                        files.classList.remove("bg-white");
                        files.classList.remove("text-black");
                        files.classList.add("bg-gray-200");
                        files.classList.add("text-gray-700");
                    });

                    files.addEventListener("click", function () {
                        resetButtons();
                        manual.classList.remove("bg-white");
                        manual.classList.remove("text-black");
                        manual.classList.add("bg-gray-200");
                        manual.classList.add("text-gray-700");
                    });
                });

                function filesAddCompetenceField() {
                    document.querySelectorAll(`[id^="competence-container"]`).forEach(div => div.remove());
                    document.querySelectorAll(`[id^="competencesContainer"]`).forEach(div => div.remove());
                    competencesData = [];

                    document.querySelector(`input[name="competences_data"]`).value = "";
                    displayCoursesCompetence(arrayFilesCourses);

                    let html = `
                        <div class="flex items-center">
                                <p>' . get_string('desc_files_competence', 'mod_studentqcm') . '</p>
                                <a href="#" onclick="showInfoJSON()">
                                    <i class="fa-solid fa-circle-info ml-2 text-indigo-600"></i>
                                </a>
                        </div>
                        <div id="drop-area-competences" class="drop-area bg-sky-50 p-6 m-4 border-2 border-dashed border-white flex flex-col justify-center items-center text-center cursor-pointer">
                            <i class="fa-solid fa-cloud-arrow-up fa-5x"></i>
                            <p>Glissez et déposer pour uploader le fichier</p>
                            <p>Ou</p>
                            <p>Charger un fichier</p>
                            <input type="file" id="fileInputCompetences" multiple hidden>
                        </div>
                        <div id="file-list-competences"></div>
                    `;
                    
                    let containerFilesCompetence = document.getElementById("choice_add_competence");
                    containerFilesCompetence.innerHTML = "";
                    containerFilesCompetence.insertAdjacentHTML("beforeend", html);

                    let indexFileCompetences = 0;
                    const dropAreaCompetences = document.getElementById("drop-area-competences");
                    const fileInputCompetences = document.getElementById("fileInputCompetences");
                    const fileListCompetences = document.getElementById("file-list-competences");

                    dropAreaCompetences.addEventListener("dragover", function(e) {
                        e.preventDefault();
                        dropAreaCompetences.style.backgroundColor = "#e9ecef";
                    });

                    dropAreaCompetences.addEventListener("dragleave", function(e) {
                        dropAreaCompetences.style.backgroundColor = "#f8f9fa";
                    });

                    dropAreaCompetences.addEventListener("drop", function(e) {
                        e.preventDefault();
                        dropAreaCompetences.style.backgroundColor = "#f8f9fa";
                        handleFilesCompetences(e.dataTransfer.files);
                    });

                    dropAreaCompetences.addEventListener("click", function() {
                        fileInputCompetences.click();
                    });

                    fileInputCompetences.addEventListener("change", function(e) {
                        handleFilesCompetences(e.target.files);
                    });

                    function handleFilesCompetences(files) {
                        let promises = [];

                        if(competencesData.length == 0) {
                            for (let file of files) {
                                let reader = new FileReader();

                                let promise = new Promise((resolve, reject) => {
                                    reader.onload = function (event) {
                                        try {
                                            let content = event.target.result;
                                            let compData = JSON.parse(content); // Lecture du JSON
                                            let cpt_comp = 0;
                                            compData.forEach(Competence => {
                                                let subCompetences = [];
                                                Competence.subCompetences.forEach(subCompetence => {
                                                    let keywords = [];
                                                    subCompetence.keywords.forEach(keyword => {
                                                        keywords.push(keyword);
                                                    });

                                                    subCompetences.push({
                                                        name: subCompetence.name,
                                                        keywords: keywords
                                                    });
                                                });

                                                let competenceData = {
                                                    id: cpt_comp,
                                                    name: Competence.name,
                                                    subCompetences: subCompetences
                                                };
                                                competencesData.push(competenceData);
                                                displayValidatedCompetences(competenceData.id);
                                                cpt_comp++;

                                            });
                                            

                                            let fileItem = document.createElement("div");
                                            fileItem.classList.add("file-item");
                                            fileItem.textContent = file.name;
                                            fileListCompetences.appendChild(fileItem);
                                            
                                            indexFileCompetences++;
                                            resolve();
                                        } catch (error) {
                                            console.error("Erreur lors du parsing du JSON :", error);
                                            reject(error);
                                        }
                                    };

                                    reader.readAsText(file); // Lecture du fichier en texte
                                });

                                promises.push(promise);
                            }
                            
                        }

                        Promise.all(promises).then(() => {
                            let hiddenInput = document.querySelector(`input[name="competences_data"]`);
                            if (hiddenInput) {
                                hiddenInput.value = JSON.stringify(competencesData);
                                displayCoursesCompetence(arrayFilesCourses);
                            }
                        });
                    }

                    
                    function importCompetencesData(event) {
                        let file = event.target.files[0]; 

                        if (!file) {
                            alert(`Veuillez sélectionner un fichier JSON !`);
                            return;
                        }

                        let reader = new FileReader();
                        reader.onload = function(e) {
                            try {
                            let competencesData = JSON.parse(e.target.result); // Convertit le JSON en objet JavaScript

                            if (!Array.isArray(competencesData)) {
                                alert("Format de fichier invalide !");
                                return;
                            }

                            competencesData.forEach(competence => {
                                let index_competence = competence.id;
                                let competenceName = competence.name;
                                let subCompetences = competence.subCompetences;

                                // Création du conteneur de la compétence
                                let competenceDiv = document.createElement("div");
                                competenceDiv.classList.add("competence-container");
                                competenceDiv.id = `competence-container${index_competence}`;
                                competenceDiv.innerHTML = `
                                    <input type="text" id="competence-name${index_competence}" value="${competenceName}" />
                                    <div id="subcompetences-container${index_competence}"></div>
                                `;

                                document.getElementById("competences-list").appendChild(competenceDiv);

                                // Ajout des sous-compétences
                                subCompetences.forEach((subCompetence, subIndex) => {
                                    let subCompetenceDiv = document.createElement("div");
                                    subCompetenceDiv.classList.add("subcompetence-container");
                                    subCompetenceDiv.id = `subcompetences-container${index_competence}${subIndex}`;
                                    subCompetenceDiv.innerHTML = `
                                        <input type="text" id="subcompetence-name${index_competence}${subIndex}" value="${subCompetence.name}" />
                                        <div id="keyword-container${index_competence}${subIndex}"></div>
                                    `;

                                competenceDiv.querySelector(`#subcompetences-container${index_competence}`).appendChild(subCompetenceDiv);

                                // Ajout des mots-clés
                                subCompetence.keywords.forEach(keyword => {
                                    let keywordInput = document.createElement("input");
                                    keywordInput.type = "text";
                                    keywordInput.value = keyword;
                                    subCompetenceDiv.querySelector(`#keyword-container${index_competence}${subIndex}`).appendChild(keywordInput);
                            });
                        });
                    });

                    } catch (error) {
                        alert(`Erreur lors de l importation du fichier : ` + error.message);
                    }
                };

                reader.readAsText(file);
                }

                document.addEventListener("DOMContentLoaded", function() {
                    document.getElementById("import-json").addEventListener("change", importCompetencesData);
                });


                }

                function manualAddCompetenceField() {
                    document.querySelectorAll(`[id^="competence-container"]`).forEach(div => div.remove());
                    document.querySelectorAll(`[id^="competencesContainer"]`).forEach(div => div.remove());
                    competencesData = [];
                    document.querySelector(`input[name="competences_data"]`).value = "";
                    displayCoursesCompetence(arrayFilesCourses);
                    competencesData = [];

                    let html= `
                        <p>' . get_string('desc_manual_competence', 'mod_studentqcm') . '</p>
                        <div class="flex justify-center items-center">
                            <button id="add_competences" onclick="addCompetenceField()" class="bg-white p-2 m-4 rounded font-bold hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md">'
                            . get_string('add_competences', 'mod_studentqcm') .
                            '</button>
                             <button type="button" id="export_competences" onclick="exportCompetencesData()" 
                                class="bg-sky-200 p-2 m-4 rounded font-bold hover:bg-sky-400 text-sky-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md">
                                ' . get_string('export', 'mod_studentqcm') . '
                            </button>
                        </div>
                    `;
                    let containerManualCompetence = document.getElementById("choice_add_competence");
                    containerManualCompetence.innerHTML = "";
                    containerManualCompetence.insertAdjacentHTML("beforeend", html);

                }
            function exportCompetencesData() {
                if (competencesData.length === 0) {
                    alert("Aucune compétence à exporter !");
                    return;
                }

                let jsonData = JSON.stringify(competencesData, null, 4);

                let blob = new Blob([jsonData], { type: "application/json" });

                let a = document.createElement("a");
                a.href = URL.createObjectURL(blob);
                a.download = "competences.json"; // Nom du fichier
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
            }
            </script>
            '
        );

        $mform->addElement('html', '<div id="validated_competences-container"></div>');

        $mform->addElement('html', '<div id="add_competences-container"></div>');

        $mform->addElement('hidden', 'competences_data');
        $mform->setType('competences_data', PARAM_RAW);

        $mform->addElement('html', '
        <div id="hidden_files_competences"></div>
        
        <script>
            let compteur_competence = 0;
            let compteur_subcompetence = 0;
            let compteur_keyword = 0;
            let competencesData = [];

            function addCompetenceField() {
                let container = document.getElementById("add_competences-container");

                let index_competence = compteur_competence;

                let fieldHTML = `
                    <div id="competence-container${index_competence}" class="competence-block p-4 border border-gray-300 rounded-lg bg-white mt-4">
                        <h3 class="text-2xl font-bold">' . get_string('competences_title', 'mod_studentqcm') . '</h3>
                        <label>' . get_string('name_competence', 'mod_studentqcm') . '</label>
                        <input type="text" id="competence-name${index_competence}" name="name_competence[]" class="form-control p-2 border rounded w-full" required>

                        <button type="button" class="bg-sky-100 p-2 m-4 rounded font-bold hover:bg-sky-300 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md" onclick="addSubCompetenceField(${index_competence})">
                            ' . get_string('add_subcompetences', 'mod_studentqcm') . '
                        </button>

                        <div id="add_subcompetences-container${index_competence}" class="mt-4"></div>
                        <div class="flex mt-4">
                            <button type="button" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded " onclick="validateCompetence(${index_competence})">
                                ' . get_string('validate', 'mod_studentqcm') . '
                            </button>
                            <button type="button" class="bg-gray-200 hover:bg-gray-400 font-bold py-2 px-4 rounded mx-4" onclick="deleteAddCompetence(${index_competence})">
                                ' . get_string('cancel', 'mod_studentqcm') . '
                            </button>
                        </div>

                    </div>
                `;

                compteur_competence += 1;
                container.insertAdjacentHTML("beforeend", fieldHTML);
            }

            function addSubCompetenceField(index_competence) {
                let container = document.getElementById(`add_subcompetences-container${index_competence}`);
                compteur_subcompetence += 1;

                let fieldHTML = `
                    <div id="subcompetences-container${index_competence}${compteur_subcompetence}" class="subcompetence-block p-4 border border-gray-300 bg-white rounded-lg mt-4">
                            <h3 class="text-2xl font-bold">' . get_string('subcompetences_title', 'mod_studentqcm') . '</h3>
                        <label>' . get_string('name_subcompetence', 'mod_studentqcm') . '</label>
                        <input type="text" id="subcompetence-name${index_competence}${compteur_subcompetence}" name="name_subcompetence[]" class="form-control p-2 border rounded w-full" required>
                        <div class="flex mt-4">
                            <button type="button" class="bg-sky-100 p-2 rounded font-bold hover:bg-sky-300 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md" onclick="addKeyword(${index_competence}, ${compteur_subcompetence})">
                                ' . get_string('add_keyword', 'mod_studentqcm') . '
                            </button>
                            <button type="button" class="bg-gray font-bold py-2 px-4 rounded mx-4" onclick="deleteAddSubCompetence(${index_competence}, ${compteur_subcompetence})">
                                ' . get_string('cancel', 'mod_studentqcm') . '
                            </button>
                        </div>

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
                        <button type="button" class="bg-gray font-bold py-2 mt-4 px-4 rounded" onclick="deleteAddKeyword(${index_competence}, ${index_subcompetence}, ${compteur_keyword})">
                                ' . get_string('cancel', 'mod_studentqcm') . '
                        </button>
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
                    id: index_competence,
                    name: competenceName,
                    subCompetences: subCompetences
                };

                competencesData.push(competenceData);

                document.querySelector(`input[name="competences_data"]`).value = JSON.stringify(competencesData);

                displayCoursesCompetence(arrayFilesCourses);

                if (competencesData.length === 1) {
                    for (let file of arrayFilesCourses) {
                        updateCompetencyName(file.id, competenceName);
                    }
                }
                // Supprimer la section et afficher les données sous forme de texte
                document.getElementById(`competence-container${index_competence}`).remove();
                displayValidatedCompetences(index_competence);

            }

            function displayValidatedCompetences(index_competence) {
                let validatedContainer = document.getElementById("validated_competences-container");
                validatedContainer.innerHTML = "";

                competencesData.forEach((competence, index) => {
                    let html = `
                        <div id="competencesContainer${index}" class="p-4 border border-gray-300 rounded-lg mt-4 bg-white">
                            <h3 class="text-2xl font-bold">${competence.name}</h3>
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
                            <button type="button" class="bg-red-500 text-white p-2 m-2 rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md" onclick="deleteCompetence(${competence.id})"> ' . get_string('delete', 'mod_studentqcm') . ' </button>

                        </div>
                    `;
                    validatedContainer.insertAdjacentHTML("beforeend", html);
                });
            }

            function deleteCompetence(indexComp) {
                let competencesContainer = document.getElementById(`competencesContainer${indexComp}`);
                competencesContainer.remove();


                competencesData = competencesData.filter(comp => comp.id !== indexComp);

                document.querySelector(`input[name="competences_data"]`).value = JSON.stringify(competencesData);
                displayCoursesCompetence(arrayFilesCourses);
            }

            function deleteAddCompetence(indexComp) {
                let competencesContainer = document.getElementById(`competence-container${indexComp}`);
                competencesContainer.remove();
            }
                
            function deleteAddSubCompetence(indexComp, indexSubcomp) {
                let subcompetencesContainer = document.getElementById(`subcompetences-container${indexComp}${indexSubcomp}`);
                subcompetencesContainer.remove();
            }

            function deleteAddKeyword(indexComp, indexSubcomp, indexKeyword) {
                let keywordContainer = document.getElementById(`keyword-container${indexComp}${indexSubcomp}${indexKeyword}`);
                keywordContainer.remove();
            }


        </script>');


        $mform->addElement('html', '<h2 class="text-3xl font-bold mt-4">' . get_string('choice_courses', 'mod_studentqcm') . '</h2>');

        $mform->addElement('hidden', 'courses_files_data');
        $mform->setType('courses_files_data', PARAM_RAW);

        $mform->addElement('html', '
        <div id="drop-area-courses" class="drop-area bg-sky-50 p-6 m-4 border-2 border-dashed border-white flex flex-col justify-center items-center text-center cursor-pointer"
            ondrop="dropHandlerCourses(event);"
            ondragleave="dragLeaveHandlerCourses(event);"
            ondragover="dragOverHandlerCourses(event);">
            <i class="fa-solid fa-cloud-arrow-up fa-5x"></i>
            <p>' . get_string('drag_drop', 'mod_studentqcm'). '</p>
            <p> ' . get_string('or', 'mod_studentqcm'). '</p>
            <p>' . get_string('upload_file', 'mod_studentqcm'). '</p>
            <input type="file" id="fileInputCourses" multiple hidden>
        </div>
        <div id="file-list-courses"></div>
        <div id="hidden_files_courses"></div>


        <script>
            let arrayFilesCourses = [];
            let indexCourses = 0;
            let indexFileCourses = 0;
                const dropAreaCourses = document.getElementById("drop-area-courses");
                const fileInputCourses = document.getElementById("fileInputCourses");
                const fileListCourses = document.getElementById("file-list-courses");
                const hiddenFilesCourses = document.getElementById("hidden_files_courses");

                function dragOverHandlerCourses(e) {
                    e.preventDefault();
                    dropAreaCourses.style.backgroundColor = "#e9ecef";
                };

                function dragLeaveHandlerCourses(e) {
                    dropAreaCourses.style.backgroundColor = "#f8f9fa"; 
                };

                function dropHandlerCourses(e) {
                    e.preventDefault();
                    dropAreaCourses.style.backgroundColor = "#f8f9fa";
                    handleFilesCourses(e.dataTransfer.files);
                };

                dropAreaCourses.addEventListener("click", function () {
                    fileInputCourses.click();
                    
                });

                fileInputCourses.addEventListener("change", function (e) {
                    handleFilesCourses(e.target.files);
                });


                async function uploadFile(file) {
                    let formData = new FormData();
                    formData.append("file", file);

                    try {
                        let response = await fetch("/mod/studentqcm/upload.php?filearea=coursefiles", {
                            method: "POST",
                            body: formData
                        });

                        if (response.ok) {
                            let result = await response.json();
                        } else {
                            console.error("Erreur lors de l\'upload, statut : " + response.status);
                        }
                    } catch (error) {
                        console.error("Erreur réseau :", error);
                    }
                }

            

                async function handleFilesCourses(files) {
                    let competences = document.querySelector(`input[name="competences_data"]`);
                    for (let file of files) {
                        let fileData = {
                            id: indexCourses, 
                            file_name: file.name,
                            competency_name: ""
                        };
                        arrayFilesCourses.push(fileData);

                        await uploadFile(file); 
        
                        if (competences.value.length>0 && competences.value!= "[]") {
                            let compData = JSON.parse(competences.value);
                            updateCompetencyName(fileData.id, compData[0].name);
                        }
                            
                        indexCourses++;
                    }
                    
                    document.querySelector(`input[name="courses_files_data"]`).value = JSON.stringify(arrayFilesCourses);
                    displayCoursesCompetence(arrayFilesCourses);  
                }

                function displayCoursesCompetence(files) {
                    
                    fileListCourses.innerHTML = "";

                    for (let file of files) {
                        let html = `
                            <div id="container-course-file-${file.id}">
                                <p class="font-bold">${file.file_name}</p>
                                <div class="ml-4 flex items-center">
                                    <p>' . get_string('choice_comp', 'mod_studentqcm') . '</p>
                                    <select name="fileCourses${file.file_name}[]" class="ml-4 bg-white p-2 border w-full max-w-[100px] rounded" required>
                                        ${generateOptionCourses()}
                                    </select>
                                    <button type="button" class="bg-red-500 text-white p-2 m-2 rounded hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md" onclick="deleteCourse(${file.id})"> ' . get_string('delete', 'mod_studentqcm') . ' </button>
                                </div>
                            </div>
                        `;
                        fileListCourses.insertAdjacentHTML("beforeend", html);

                        let selectElement = document.querySelector(`#container-course-file-${file.id} select`);
                        selectElement.addEventListener("change", function() {
                            updateCompetencyName(file.id, this.value);
                        });
                    }
                }

                function updateCompetencyName(fileId, selectElement) {
                    let file = arrayFilesCourses.find(f => f.id === fileId);
                    if(file) {
                        file.competency_name = selectElement;     
                    } 
                    document.querySelector(`input[name="courses_files_data"]`).value = JSON.stringify(arrayFilesCourses);
                }

       
                function deleteCourse(fileId) {
                    arrayFilesCourses = arrayFilesCourses.filter(file => file.id !== fileId);
                    document.getElementById(`container-course-file-${fileId}`).remove();
                    document.querySelector(`input[name="courses_files_data"]`).value = JSON.stringify(arrayFilesCourses);
                }



            function generateOptionCourses() {
                let arrayComp = [];
                let competences = document.querySelector(`input[name="competences_data"]`);
                let optionsPop = "";
                if (competences.value.length>0 && competences.value!= "[]") {
                    let compData = JSON.parse(competences.value);
                    let length = compData.length;

                    for(let i=0; i<length; i++) {
                        optionsPop += `<option value="${compData[i].id}">${compData[i].name}</option>`;
                    };

                    return optionsPop;

                } else {
                    let html = `<option value="0">Aucune compétence n\'a été créé</option>`;
                    return html;
                }
                
            }

        </script>
    ');

        $mform->addElement('html', '</div>');


        $mform->addElement('html', '<div class="mb-8 rounded-2xl p-4 bg-sky-100">');

        $mform->addElement('hidden', 'checkbox_tt_data');
        $mform->setType('checkbox_tt_data', PARAM_RAW);

        $mform->addElement('html', '<h2 class="mb-4 text-3xl font-bold">' . get_string('phases_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('html', '
            <style>
                #id_add_tiers_temps_phase + div {
                    width: 2.75rem;
                    height: 1.5rem;
                    background-color: #d1d5db;
                    border-radius: 9999px;
                    position: relative;
                    transition: background-color 0.3s;
                }
                #id_add_tiers_temps_phase:checked + div {
                    background-color:rgb(21, 187, 35);
                }
                #id_add_tiers_temps_phase + div::after {
                    content: "";
                    position: absolute;
                    left: 0.25rem;
                    top: 0.25rem;
                    width: 1rem;
                    height: 1rem;
                    background-color: white;
                    border-radius: 50%;
                    transition: transform 0.3s;
                }
                #id_add_tiers_temps_phase:checked + div::after {
                    transform: translateX(1.25rem);
                }
            </style>

            <label class="relative inline-flex items-center cursor-pointer">
                <p class="m-2">' . get_string('add_tiers_temps_phase', 'mod_studentqcm') . '</p>
                <input type="checkbox" name="add_tiers_temps_phase" id="id_add_tiers_temps_phase" class="sr-only peer">
                <div></div>
            </label>

            <input type="hidden" name="add_tiers_temps_phase_hidden" id="add_tiers_temps_phase_hidden" value="0">

            <script>
                document.addEventListener("DOMContentLoaded", function() {
                var checkbox = document.getElementById("id_add_tiers_temps_phase");
                var tiersElements = [];

                    tiersElements = document.querySelectorAll(`[id^="fitem_id_end_date_tt_"], [id="timeSelectorContainer_tt"]`);


                    function toggleTiersTemps() {
                        tiersElements.forEach(elt => {
                            elt.style.display = checkbox.checked ? "flex" : "none";
                        });
                        let checkbox_tt = document.querySelector(`[name="checkbox_tt_data"]`);
                        checkbox_tt.value = checkbox.checked;
                    }

                    checkbox.addEventListener("change", toggleTiersTemps);
                    toggleTiersTemps();
                });

            </script>
        ');



        $mform->addElement('html', '
        <style>
            #fitem_id_start_date_1, #fitem_id_start_date_2, #fitem_id_start_date_3,
            #fitem_id_end_date_1, #fitem_id_end_date_2, #fitem_id_end_date_3,
            #fitem_id_end_date_tt_1, #fitem_id_end_date_tt_2, #fitem_id_end_date_tt_3 {
                margin: 0;
            }
        </style>
        ');

        $mform->addElement('hidden', 'hours_minutes_data');
        $mform->setType('hours_minutes_data', PARAM_RAW);

        $mform->addElement('html', '<div class="bg-sky-200 py-2 rounded text-sky-700 my-4">');
            $mform->addElement('html', '<div class=" m-3">');
            $mform->addElement('date_selector', 'start_date_1', get_string('start_date_1', 'mod_studentqcm'));
            $mform->addRule('start_date_1', null, 'required', null, 'client');  

            $mform->addElement('html', '
            <div id="timeSelectorContainer_start" class="flex gap-2 m-1">
                <select id="start_hour_1" name="start_hour_1[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">h</p>

                <select id="start_minute_1" name="start_minute_1[]" class="form-control p-2 rounded w-fullmax-w-[30]" required></select>
                <p class="mt-2">min</p>
            </div>


            <script>

                let arrayHourMinute = {};
                let hoursMinutesData = document.querySelector(`input[name="hours_minutes_data"]`);

                // Générer les options pour les heures et minutes
                const optionsHours = Array.from({ length: 24 }, (_, i) => i);
                const optionsMinutes = Array.from({ length: 12 }, (_, i) => i * 5);

                function generateOptions(selectId, options, defaultValue) {
                    const select = document.getElementById(selectId);
                    if (!select) return; 

                    select.innerHTML = ""; 
                    options.forEach(value => {
                        const option = document.createElement("option");
                        option.value = value;
                        option.textContent = value.toString().padStart(2, "0"); // Format "00", "01", ...
                        
                        // Appliquer la valeur par défaut
                        if (value === defaultValue) {
                            option.selected = true;
                        }

                        select.appendChild(option);
                    });
                }
                function saveHourMinute(type) {
                    if (type === "start") {
                        arrayHourMinute["start"] = [];
                        let hourMinute = document.querySelectorAll("#timeSelectorContainer_start"); // Sélectionne tous les blocs d\'heure et minute

                        hourMinute.forEach((hmBlock, index) => {
                        // Récupérer les valeurs sélectionnées pour hour et minute
                           let hourValue = hmBlock.querySelector(`select[name="start_hour_${index+1}[]"]`).value;
                            let minuteValue = hmBlock.querySelector(`select[name="start_minute_${index+1}[]"]`).value;

                            // Stocker dans un objet
                            let hmData = {
                                hour: hourValue,
                                minute: minuteValue
                            };

                            arrayHourMinute["start"].push(hmData);
                        });
                    } else if (type === "end") {
                        arrayHourMinute["end"] = [];
                        let hourMinute = document.querySelectorAll("#timeSelectorContainer_end"); // Sélectionne tous les blocs d\'heure et minute

                        hourMinute.forEach((hmBlock, index) => {
                            // Récupérer les valeurs sélectionnées pour hour et minute
                            let hourValue = hmBlock.querySelector(`select[name="end_hour_${index+1}[]"]`).value;
                            let minuteValue = hmBlock.querySelector(`select[name="end_minute_${index+1}[]"]`).value;

                            // Stocker dans un objet
                            let hmData = {
                                hour: hourValue,
                                minute: minuteValue
                            };

                            arrayHourMinute["end"].push(hmData);
                        });
                    } else {
                        arrayHourMinute["tt"]= [];
                        let hourMinute = document.querySelectorAll("#timeSelectorContainer_tt"); // Sélectionne tous les blocs d\'heure et minute

                        hourMinute.forEach((hmBlock, index) => {
                            // Récupérer les valeurs sélectionnées pour hour et minute
                            let hourValue = hmBlock.querySelector(`select[name="end_hour_tt_${index+1}[]"]`).value;
                            let minuteValue = hmBlock.querySelector(`select[name="end_minute_tt_${index+1}[]"]`).value;

                            // Stocker dans un objet
                            let hmData = {
                                hour: hourValue,
                                minute: minuteValue
                            };

                            arrayHourMinute["tt"].push(hmData);
                        });
                    }
                        
                    console.log("arrayHourMinute: ", arrayHourMinute);

                    hoursMinutesData.value = JSON.stringify(arrayHourMinute);
                }

                // Exécuter la génération après chargement du DOM
                document.addEventListener("DOMContentLoaded", () => {
                    generateOptions("start_hour_1", optionsHours, 8); // 8h par défaut
                    generateOptions("start_minute_1", optionsMinutes, 0); // 00 min par défaut
                });
            </script>

            ');
            $mform->addElement('html', '</div>');


            $mform->addElement('html', '<div class=" m-3">');
            $mform->addElement('date_selector', 'end_date_1', get_string('end_date_1', 'mod_studentqcm'));
            $mform->addRule('end_date_1', null, 'required', null, 'client');

            $mform->addElement('html', '
            <div id="timeSelectorContainer_end" class="flex gap-2 m-1">
                <select id="end_hour_1" name="end_hour_1[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">h</p>

                <select id="end_minute_1" name="end_minute_1[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">min</p>
            </div>

            <script>

                // Exécuter la génération après chargement du DOM
                document.addEventListener("DOMContentLoaded", () => {
                    generateOptions("end_hour_1", optionsHours, 8);
                    generateOptions("end_minute_1", optionsMinutes, 0);
                });
            </script>
            ');
            $mform->addElement('html', '</div>');

            $mform->addElement('html', '<div class=" m-3">');
            $mform->addElement('date_selector', 'end_date_tt_1', get_string('end_date_tt_1', 'mod_studentqcm'), array('class' => 'tiers-temps'));
            $mform->addRule('end_date_tt_1', null, 'required', null, 'client');
            $mform->addElement('html', '
            <div id="timeSelectorContainer_tt" class="flex gap-2 m-1">
                <select id="end_hour_tt_1" name="end_hour_tt_1[]" class="form-control p-2 rounded max-w-[30]" required></select>
                <p class="mt-2">h</p>

                <select id="end_minute_tt_1" name="end_minute_tt_1[]" class="form-control p-2 rounded max-w-[30]" required></select>
                <p class="mt-2">min</p>
            </div>

            <script>

                // Exécuter la génération après chargement du DOM
                document.addEventListener("DOMContentLoaded", () => {
                    generateOptions("end_hour_tt_1", optionsHours, 8);
                    generateOptions("end_minute_tt_1", optionsMinutes, 0);
                });
            </script>
            ');
            $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="bg-lime-200 py-2 rounded text-lime-700 my-4">');
            $mform->addElement('html', '<div class="m-3">');
            $mform->addElement('date_selector', 'start_date_2', get_string('start_date_2', 'mod_studentqcm'));
            $mform->addRule('start_date_2', null, 'required', null, 'client');
            $mform->addElement('html', '
            <div id="timeSelectorContainer_start" class="flex gap-2 m-1">
                <select id="start_hour_2" name="start_hour_2[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">h</p>

                <select id="start_minute_2" name="start_minute_2[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">min</p>
            </div>

            <script>

                // Exécuter la génération après chargement du DOM
                document.addEventListener("DOMContentLoaded", () => {
                    generateOptions("start_hour_2", optionsHours, 8);
                    generateOptions("start_minute_2", optionsMinutes, 0);
                });
            </script>
            ');
            $mform->addElement('html', '</div>');

            $mform->addElement('html', '<div class="m-3">');
            $mform->addElement('date_selector', 'end_date_2', get_string('end_date_2', 'mod_studentqcm'));
            $mform->addRule('end_date_2', null, 'required', null, 'client');
            $mform->addElement('html', '
            <div id="timeSelectorContainer_end" class="flex gap-2 m-1">
                <select id="end_hour_2" name="end_hour_2[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">h</p>

                <select id="end_minute_2" name="end_minute_2[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">min</p>
            </div>

            <script>

                // Exécuter la génération après chargement du DOM
                document.addEventListener("DOMContentLoaded", () => {
                    generateOptions("end_hour_2", optionsHours, 8);
                    generateOptions("end_minute_2", optionsMinutes, 0);
                });
            </script>
            ');
            $mform->addElement('html', '</div>');

            $mform->addElement('html', '<div class=" m-3">');
            $mform->addElement('date_selector', 'end_date_tt_2', get_string('end_date_tt_2', 'mod_studentqcm'), array('class' => 'tiers-temps'));
            $mform->addRule('end_date_tt_2', null, 'required', null, 'client');
            $mform->addElement('html', '
            <div id="timeSelectorContainer_tt" class="flex gap-2 m-1">
                <select id="end_hour_tt_2" name="end_hour_tt_2[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">h</p>

                <select id="end_minute_tt_2" name="end_minute_tt_2[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">min</p>
            </div>

            <script>

                // Exécuter la génération après chargement du DOM
                document.addEventListener("DOMContentLoaded", () => {
                    generateOptions("end_hour_tt_2", optionsHours, 8);
                    generateOptions("end_minute_tt_2", optionsMinutes, 0);
                });
            </script>
            ');
            $mform->addElement('html', '</div>');
        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="bg-indigo-200 py-2 rounded text-indigo-700 my-4">');
            $mform->addElement('html', '<div class="m-3">');
            $mform->addElement('date_selector', 'start_date_3', get_string('start_date_3', 'mod_studentqcm'));
            $mform->addRule('start_date_3', null, 'required', null, 'client');
            $mform->addElement('html', '
            <div id="timeSelectorContainer_start" class="flex gap-2 m-1">
                <select id="start_hour_3" name="start_hour_3[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">h</p>

                <select id="start_minute_3" name="start_minute_3[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">min</p>
            </div>

            <script>

                // Exécuter la génération après chargement du DOM
                document.addEventListener("DOMContentLoaded", () => {
                    generateOptions("start_hour_3", optionsHours, 8);
                    generateOptions("start_minute_3", optionsMinutes, 0);
                    saveHourMinute("start");
                });
            </script>
            ');
            $mform->addElement('html', '</div>');

            $mform->addElement('html', '<div class=" m-3">');
            $mform->addElement('date_selector', 'end_date_3', get_string('end_date_3', 'mod_studentqcm'));
            $mform->addRule('end_date_3', null, 'required', null, 'client');
            $mform->addElement('html', '
            <div id="timeSelectorContainer_end" class="flex gap-2 m-1">
                <select id="end_hour_3" name="end_hour_3[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">h</p>

                <select id="end_minute_3" name="end_minute_3[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">min</p>
            </div>

            <script>

                // Exécuter la génération après chargement du DOM
                document.addEventListener("DOMContentLoaded", () => {
                    generateOptions("end_hour_3", optionsHours, 8);
                    generateOptions("end_minute_3", optionsMinutes, 0);
                    saveHourMinute("end");
                });
            </script>
            ');
            $mform->addElement('html', '</div>');

            $mform->addElement('html', '<div class=" m-3">');
            $mform->addElement('date_selector', 'end_date_tt_3', get_string('end_date_tt_3', 'mod_studentqcm'), array('class' => 'tiers-temps'));

            $mform->addElement('html', '
            <div id="timeSelectorContainer_tt" class="flex gap-2 m-1">
                <select id="end_hour_tt_3" name="end_hour_tt_3[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">h</p>

                <select id="end_minute_tt_3" name="end_minute_tt_3[]" class="form-control p-2 rounded w-full max-w-[30]" required></select>
                <p class="mt-2">min</p>
            </div>

            <script>

                // Exécuter la génération après chargement du DOM
                document.addEventListener("DOMContentLoaded", () => {
                    generateOptions("end_hour_tt_3", optionsHours, 8);
                    generateOptions("end_minute_tt_3", optionsMinutes, 0);
                    saveHourMinute("tt");
                });
            </script>
            ');
            $mform->addElement('html', '</div>');

        $mform->addElement('html', '<script>
        document.addEventListener("DOMContentLoaded", () => {

            let selectElementStart = document.querySelectorAll(`[id^="start_hour_"], [id^="start_minute_"]`);
            selectElementStart.forEach(select => {
                select.addEventListener("change", function() {
                    saveHourMinute("start");
                });
            });
            let selectElementEnd = document.querySelectorAll(`[id^="end_hour_"], [id^="end_minute_"]`);
            selectElementEnd.forEach(select => {
                select.addEventListener("change", function() {
                    saveHourMinute("end");
                });
            });
            let selectElementTT = document.querySelectorAll(`[id^="end_hour_tt_"], [id^="end_minute_tt_"]`);
            selectElementTT.forEach(select => {
                select.addEventListener("change", function() {
                    saveHourMinute("tt");
                });
            });

            let endDateSelect = document.querySelectorAll(`[id^="fitem_id_end_date_tt_"]`);
            console.log("endDateSelect: ", endDateSelect);
        });

        </script>
        ');
        $mform->addElement('html', '</div>');


        $mform->addElement('html', '</div>');


        // Choix types éval
        $mform->addElement('html', '<div class="mb-8 rounded-2xl p-4 bg-sky-100">');

        $options = ['0' => '0', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17', '18' => '18'];

        $mform->addElement('html', '<h2 class="mb-5 text-3xl font-bold">' . get_string('type_eval_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('select', 'choix_qcu', get_string('nb_qcu', 'mod_studentqcm'), $options);
        $mform->addElement('select', 'choix_qcm', get_string('nb_qcm', 'mod_studentqcm'), $options);
        $mform->addElement('select', 'choix_tcs', get_string('nb_tcs', 'mod_studentqcm'), $options);
        $mform->addElement('select', 'choix_pop', get_string('nb_pop', 'mod_studentqcm'), $options);

        $mform->addElement('html', '<div id="popOption_qcm_qcu-container"></div>');

        $mform->addElement('hidden', 'pops_data');
        $mform->setType('pops_data', PARAM_RAW);

        // Ajout du script JavaScript
        $mform->addElement('html', '
         <script>

            let arrayPOP= [];
        
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

                function savePOPData() {
                    let pops = document.querySelectorAll(".pop-block"); // Sélectionne tous les blocs POP
                    arrayPOP = []; // Réinitialise le tableau avant de le remplir

                    pops.forEach((popBlock, index) => {
                        // Récupérer les valeurs sélectionnées pour QCU et QCM
                        let qcuValue = popBlock.querySelector(`select[name="pop_qcu${index}[]"]`).value;
                        let qcmValue = popBlock.querySelector(`select[name="pop_qcm${index}[]"]`).value;

                        // Stocker dans un objet
                        let popData = {
                            qcu: qcuValue,
                            qcm: qcmValue
                        };

                        arrayPOP.push(popData);
                    });

                    document.querySelector(`input[name="pops_data"]`).value = JSON.stringify(arrayPOP);
                }

                function displayPOP() {
                    let pop = parseInt(popSelect.value) || 0;
                    let popContainer = document.getElementById("popOption_qcm_qcu-container");
                    popContainer.innerHTML = "";

                    for (let i = 0; i < pop; i++) {
                        let fieldHTML = `
                            <div class="pop-block p-4 border border-gray-300 rounded-lg mt-4 bg-white">
                                <h3 class="text-xl font-bold">POP ${i + 1}</h3>
                                <label>' . get_string('nb_qcu', 'mod_studentqcm') . '</label>
                                <select name="pop_qcu${i}[]" class="form-control p-2 border rounded w-full" required>
                                    ${generateOptionPOPQCU(i)}
                                </select>
                                <label>' . get_string('nb_qcm', 'mod_studentqcm') . '</label>
                                <select name="pop_qcm${i}[]" class="form-control p-2 border rounded w-full" required>
                                    ${generateOptionPOPQCM(i)}
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

                    const POPqcuSelect = document.querySelector(`select[name="pop_qcu${index_pop}[]"]`);
                    let POPqcu = parseInt(POPqcuSelect?.value) || 0;

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

                    selects.forEach(select => {
                        let currentValue = parseInt(select.value) || 0;
                        select.innerHTML = ""; // Réinitialiser les options
                        if (select.name == "choix_pop") {
                            let value = remaining < currentValue ? currentValue : remaining;
                            for (let i = 0; i <= value; i++) {
                                let option = document.createElement("option");
                                option.value = i;
                                option.textContent = i;
                                select.appendChild(option);
                            }
                        } else {
                            for (let i = 0; i <= remaining + currentValue; i++) {
                                let option = document.createElement("option");
                                option.value = i;
                                option.textContent = i;
                                select.appendChild(option);
                            }
                        }
                        
                        select.value = currentValue;
                    });

                    document.querySelectorAll("#popOption_qcm_qcu-container select").forEach(select => {
                        let currentValue = parseInt(select.value) || 0;
                        arrayPOP = [];
                        
                        let match = select.name.match(/\d+/);  // Cherche un nombre dans le name
                        let indexPOP = match ? match[0] : null;
                        if (select.name.includes("qcm")) {
                            select.innerHTML = generateOptionPOPQCM(indexPOP);
                        } else {
                            select.innerHTML = generateOptionPOPQCU(indexPOP);
                        }  
                        const POPqcuSelect = document.querySelector(`select[name="pop_qcu${indexPOP}[]"]`);
                        let POPqcu = parseInt(POPqcuSelect?.value) || 0;

                        const POPqcmSelect = document.querySelector(`select[name="pop_qcm${indexPOP}[]"]`);
                        let POPqcm = parseInt(POPqcmSelect?.value) || 0;

                        let pop = {
                            nb_qcm: POPqcm,
                            nb_qcu: POPqcu
                        };
                        arrayPOP.push(pop);

                        select.value = currentValue;
                    });
                    
                }

                document.addEventListener("change", (event) => {
                    if (event.target.matches(`select[name^="pop_qcu"], select[name^="pop_qcm"]`)) {
                        savePOPData();
                    }
                });

                selects.forEach(select => {
                    select.addEventListener("change", updateOptions);
                });
                
                updateOptions();

                popSelect.addEventListener("change", displayPOP);

                displayPOP();

            });
        </script>');

        $mform->addElement('html', '
        <style>
            #fitem_id_nb_reviewer {
                font-weight: bold !important;
                margin-top: 30px !important;
            }
        </style>
        ');
        $mform->addElement('select', 'nb_reviewer', get_string('nb_reviewer', 'mod_studentqcm'), ['1'=>'1', '2'=>'2', '3'=>'3']);

        $mform->addElement('html', '</div>');

        $mform->addElement('html', '<div class="mb-8 rounded-2xl p-4 bg-sky-100">');

        $this->standard_coursemodule_elements();

        // Boutons d'action (enregistrer, annuler)
        $mform->addElement('html', '
            <script>
            document.addEventListener("DOMContentLoaded", function() {
                let form = document.querySelector(".mform"); // Sélection du formulaire Moodle

                form.addEventListener("submit", function(event) {
                    // recherche si au moins une compétence a été rempli
                    let errors = [];

                    let comp_data = document.querySelector(`input[name="competences_data"]`);
                    
                    if (comp_data.value.trim() === "" || comp_data.value.trim() === "[]") {
                        errors.push("Veuillez saisir au moins une compétence.");
                    }

                    // Si des erreurs sont détectées, empêcher la soumission et afficher les erreurs
                    if (errors.length > 0) {
                        event.preventDefault();
                        alert(errors.join("\n"));
                    }
                    
                });
            });
            </script>

        ');
        $this->add_action_buttons();
        $mform->addElement('html', '</div>');
    }

    // Fonction pour gérer la création d'un nouveau référentiel, compétence, sous-compétence et mot-clé
    public function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        // Création d'un référentiel si nécessaire
        if (!empty($data['new_referentiel'])) {
            $referentiel = new stdClass();
            $referentiel->name = $data['new_referentiel'];
            $DB->insert_record('referentiel', $referentiel);
            // On met à jour la sélection du référentiel avec l'ID du nouvel élément créé
            $data['referentiel_id'] = $DB->get_insertid();
        }

        // Création de compétences si nécessaire
        if (!empty($data['new_competency'])) {
            $competency = new stdClass();
            $competency->name = $data['new_competency'];
            $competency->referentiel = $data['referentiel_id'];
            $DB->insert_record('competency', $competency);
            // On met à jour la sélection de la compétence avec l'ID du nouvel élément créé
            $data['competency_id'] = $DB->get_insertid();
        }

        // Création de sous-compétences si nécessaire
        if (!empty($data['new_subcompetency'])) {
            $subcompetency = new stdClass();
            $subcompetency->name = $data['new_subcompetency'];
            $subcompetency->competency = $data['competency_id'];
            $DB->insert_record('subcompetency', $subcompetency);
            // On met à jour la sélection de la sous-compétence avec l'ID du nouvel élément créé
            $data['subcompetency_id'] = $DB->get_insertid();
        }

        // Création de mots-clés si nécessaire
        if (!empty($data['new_keyword'])) {
            $keyword = new stdClass();
            $keyword->word = $data['new_keyword'];
            $keyword->subcompetency = $data['subcompetency_id'];
            $DB->insert_record('keyword', $keyword);
            // On met à jour la sélection du mot-clé avec l'ID du nouvel élément créé
            $data['keyword_id'] = $DB->get_insertid();
        }

        return $errors;
    }
}
?>
