document.addEventListener("DOMContentLoaded", function () {
    // Fonction pour ajouter un champ de compétence dynamiquement
    window.addCompetenceField = function () {
        let container = document.getElementById("add_competences-container");

        let fieldHTML = `
            <div class="competence-block p-4 border border-gray-300 rounded-lg mt-4">
                <h3 class="text-2xl font-bold"><?php echo get_string('info_competence', 'mod_studentqcm'); ?></h3>
                <label>
                    <?php echo get_string('name_competence', 'mod_studentqcm'); ?>
                </label>
                <input type="text" name="name_competence[]" class="form-control p-2 border rounded w-full" required>

                <button type="button" id="add_subcompetences" class="bg-gray-200 font-bold py-2 px-4 rounded" onclick="addSubCompetenceField()">
                    <?php echo get_string('add_subcompetences', 'mod_studentqcm'); ?>
                </button>

                <!-- Conteneur pour les sous-compétences -->
                <div id="add_subcompetences-container" class="mt-4"></div>
            </div>
        `;

        container.insertAdjacentHTML("beforeend", fieldHTML);
    };
});

document.addEventListener("DOMContentLoaded", function () {
    // Fonction pour ajouter un champ de compétence dynamiquement
    window.addSubCompetenceField = function () {
        let container = document.getElementById("add_subcompetences-container");
        console.log("Click on button 'Ajouter une sous-compétence'");
        let fieldHTML = `
            <div class="subcompetence-block p-4 border border-gray-300 rounded-lg mt-4">
                <h3 class="text-2xl font-bold">Nouvelle Sous-Compétence</h3>
                <label>Nom de la sous-compétence</label>
                <input type="text" name="name_competence[]" class="form-control p-2 border rounded w-full" required>

                <button type="button" id="add_keyword" class="bg-gray-200 font-bold py-2 px-4 rounded" onclick="addKeyword()">
                    Ajouter un mot clef
                </button>

                <!-- Conteneur pour les sous-compétences -->
                <div id="add_keyword-container" class="mt-4"></div>

                <button type="button" id="validate_competence" class="bg-gray-200 font-bold py-2 px-4 rounded">
                    Valider
                </button>
            </div>
        `;

        container.insertAdjacentHTML("beforeend", fieldHTML);
    };
});

document.addEventListener("DOMContentLoaded", function () {
    // Fonction pour ajouter un champ de compétence dynamiquement
    window.addKeyword = function () {
        let container = document.getElementById("add_keyword-container");
        console.log("Click on button 'Ajouter un mot clef'");
        let fieldHTML = `
            <div class="keyword-block p-4 border border-gray-300 rounded-lg mt-4">
                <h3 class="text-2xl font-bold">Nouveau mot-clef</h3>
                <label>Nom du mot-clef</label>
                <input type="text" name="name_competence[]" class="form-control p-2 border rounded w-full" required>
            </div>
        `;

        container.insertAdjacentHTML("beforeend", fieldHTML);
    };
});