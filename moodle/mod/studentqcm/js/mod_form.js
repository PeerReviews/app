document.addEventListener("DOMContentLoaded", function () {
    // Fonction pour ajouter un champ de compétence dynamiquement
    window.addCompetenceField = function () {
        let container = document.getElementById("add_competences-container");

        let fieldHTML = `
            <div class="competence-block p-4 border border-gray-300 rounded-lg mt-4">
                <h3 class="text-2xl font-bold">Nouvelle Compétence</h3>
                <label><?php echo get_string('name_competence', 'mod_studentqcm'); ?></label>
                <input type="text" name="name_competence[]" class="form-control p-2 border rounded w-full" required>

                <button type="button" id="add_competences" class="bg-gray-200 text-white font-bold py-2 px-4 rounded">
                    <?php echo get_string('add_subcompetences', 'mod_studentqcm'); ?>
                </button>

                <!-- Conteneur pour les sous-compétences -->
                <div class="sous-competences-container mt-4"></div>
            </div>
        `;

        container.insertAdjacentHTML("beforeend", fieldHTML);
    };
});

