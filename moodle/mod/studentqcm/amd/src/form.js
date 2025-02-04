define(['jquery'], function($) {
    return {
        init: function() {
            $(document).ready(function() {
                let competencyCount = 0;

                $('#add-competency-btn').on('click', function() {
                    competencyCount++;
                    let competencyHtml = `
                        <div class="competency-group" id="competency-${competencyCount}">
                            <input type="text" name="competency_name[]" placeholder="Nom de la compétence" required>
                            <button type="button" class="add-subcompetency-btn" data-competency="${competencyCount}">+ Ajouter Sous-compétence</button>
                            <div class="subcompetencies-container" id="subcompetencies-${competencyCount}"></div>
                        </div>
                    `;
                    $('#competencies-container').append(competencyHtml);
                });

                $(document).on('click', '.add-subcompetency-btn', function() {
                    let competencyId = $(this).data('competency');
                    let subcompetencyCount = $(`#subcompetencies-${competencyId} .subcompetency-group`).length + 1;

                    let subcompetencyHtml = `
                        <div class="subcompetency-group" id="subcompetency-${competencyId}-${subcompetencyCount}">
                            <input type="text" name="subcompetency_name[${competencyId}][]" placeholder="Nom de la sous-compétence" required>
                            <button type="button" class="add-keyword-btn" data-competency="${competencyId}" data-subcompetency="${subcompetencyCount}">+ Ajouter Mot-clé</button>
                            <div class="keywords-container" id="keywords-${competencyId}-${subcompetencyCount}"></div>
                        </div>
                    `;
                    $(`#subcompetencies-${competencyId}`).append(subcompetencyHtml);
                });

                $(document).on('click', '.add-keyword-btn', function() {
                    let competencyId = $(this).data('competency');
                    let subcompetencyId = $(this).data('subcompetency');

                    let keywordHtml = `<input type="text" name="keyword_name[${competencyId}][${subcompetencyId}][]" placeholder="Mot-clé" required>`;
                    $(`#keywords-${competencyId}-${subcompetencyId}`).append(keywordHtml);
                });
            });
        }
    };
});
