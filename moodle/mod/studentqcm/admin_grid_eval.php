<?php

require_once(__DIR__ . '/../../config.php');

$id = required_param('id', PARAM_INT);

// Récupération du module, cours 
$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/admin_grid_eval.php', array('id' => $id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('grid_eval_title', 'mod_studentqcm') . "</p>";
echo "</div>";

// Bouton retour
echo "<div class='flex mt-8 text-lg justify-between'>";
echo "<a href='view.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 text-gray-500 no-underline'>";
echo "<i class='fas fa-arrow-left mr-2'></i>";
echo get_string('back', 'mod_studentqcm');
echo "</a>";
echo "</div>";

$studentqcm = $DB->get_record('studentqcm', ['archived' => 0], '*', IGNORE_MULTIPLE);

$grid_eval_qcu = null;
$grid_eval_qcm = null;
$grid_eval_tcs = null;

$gridData = array(
    "qcu" => null,
    "qcm" => null,
    "tcs" => null
);

if ($studentqcm) {
    // Récupérer les grilles associées aux QCU, QCM et TCS
    $grid_eval_qcu = $DB->get_record('grid_eval', ['id' => $studentqcm->grid_eval_qcu]);
    $grid_eval_qcm = $DB->get_record('grid_eval', ['id' => $studentqcm->grid_eval_qcm]);
    $grid_eval_tcs = $DB->get_record('grid_eval', ['id' => $studentqcm->grid_eval_tcs]);

    echo "<div class='grid grid-cols-3 gap-4 mt-8'>";

    $grilles = [
        ["data" => $grid_eval_qcu, "type" => "qcu", "colorbg" => "lime-200", "colortext" => "lime-600", "icon" => "fas", "title" => 'grid_eval_qcu'],
        ["data" => $grid_eval_qcm, "type" => "qcm", "colorbg" => "indigo-200", "colortext" => "indigo-600", "icon" => "fas", "title" => 'grid_eval_qcm'],
        ["data" => $grid_eval_tcs, "type" => "tcs", "colorbg" => "sky-200", "colortext" => "sky-600", "icon" => "fas", "title" => 'grid_eval_tcs']
    ];

    foreach ($grilles as $grille) {
        $grid_eval = $grille['data'];
        $type = $grille['type'];
        $colorBg = $grille['colorbg'];
        $colorText = $grille['colortext'];
        $iconClass = $grille['icon'];
        $titleString = $grille['title'];

        echo "<div id='grid-$type' class='bg-$colorBg shadow-md rounded-3xl p-4'>";
        echo "<p class='font-bold text-2xl m-8 text-$colorText text-center'>" . get_string($titleString, 'mod_studentqcm') . "</p>";
        echo "<div id='affichage-grid-$type' class='mt-4'>";

        $index = 0;
        foreach ($grid_eval as $element) {
            // Bonus section
            if ($index < 6 && $index != 0) {
                $gridData[$type]["bonus"][$index - 1] = htmlspecialchars($element, ENT_QUOTES);
                echo "<div class='items-center'>";
                echo "<label>";
                echo "<i class='$iconClass fa-plus text-lime-600'></i>";
                echo "<i class='fas fa-1 mr-3 text-lime-600'></i>";
                echo "<input class='text-md rounded border px-2' type='text' name='$type-bonus$index' value='" . htmlspecialchars($element, ENT_QUOTES) . "' oninput='updateHiddenGridData()'>";
                echo "</label>";
                echo "</div>";
            }

            // Malus section
            if ($index >= 6 && $index != 0) {
                if ($element) {
                    $gridData[$type]["malus"][$index - 6] = htmlspecialchars($element, ENT_QUOTES);
                    $index_malus = $index - 5;
                    echo "<div class='items-center'>";
                    echo "<label>";
                    echo "<i class='fas fa-minus text-red-600'></i>";
                    echo "<i class='fas fa-1 mr-3 text-red-600'></i>";
                    echo "<input class='texte-md rounded border px-2' type='text' name='$type-malus$index_malus' value='" . htmlspecialchars($element, ENT_QUOTES) . "' oninput='updateHiddenGridData()'>";
                    echo "</label>";
                    echo "</div>";
                }
            }
            $index++;
        }

        echo "</div>";
        echo "</div>";
    }

    echo "</div>";

}

echo "<div class='flex mt-4 justify-center'>";
echo "<button type='button' id='export-grid' class='font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-2xl shadow-md py-2 px-4 m-4' onclick='exportGrid()'>
    " . get_string('export_grid', 'mod_studentqcm') . "
    </button>";
echo "<button type='button' id='import-grid' class='font-bold text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-2xl shadow-md py-2 px-4 m-4' onclick='importGrid()'>
    " . get_string('import_grid', 'mod_studentqcm') . "
    </button>";
echo "</div>";
echo "<div id='import_grid'></div>";

echo "<div class='flex mr-6 justify-end'>";
echo '<form action="admin_grid_eval_save.php?id=' . $id . '" method="post" style="display:inline;">';
echo '<input type="hidden" id="gridDataHidden" name="gridData" value="">';
echo "<button type='submit' id='save-grid' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-lime-200 hover:bg-lime-300 cursor-pointer text-lime-700 no-underline text-lg' onclick='saveGrid()'>
        " . get_string('save', 'mod_studentqcm') . "
        </button>";
echo "</form>";
echo "</div>";

echo $OUTPUT->footer();
?>

<script>
    let gridData = <?php echo json_encode($gridData); ?>;
    function updateHiddenGridData() {
        let updatedGridData = <?php echo json_encode($gridData); ?>;
        Object.entries(gridData).forEach(([gridType, grid_data]) => {
            // Parcourir les types de valeurs (bonus/malus)
            Object.entries(grid_data).forEach(([type, elements]) => {
                if (type === 'bonus' || type === 'malus') {
                    elements.forEach((element, index) => {
                        let inputName = `${gridType}-${type}${index + 1}`; // Ex: "qcu_bonus1", "qcm_malus2"
                        let inputField = document.querySelector(`input[name="${inputName}"]`);

                        if (inputField) {
                            gridData[gridType][type][index] = inputField.value;
                        } else {
                            console.warn(`Champ ${inputName} non trouvé dans le DOM.`);
                        }
                    });
                }
            });
        });
        console.log("gridData: ", gridData);
        // Mettre à jour le champ caché avec les nouvelles données
        document.getElementById('gridDataHidden').value = JSON.stringify(gridData);
    }
    

    function importGrid() {
        let importContainer = document.getElementById("import_grid");
        importContainer.innerHTML = "";

        let html = `
            <p><?php echo "" . get_string('import_texte', 'mod_studentqcm') . "" ?></p>
            <div id="drop-area-grid" class="drop-area bg-sky-50 p-6 m-4 border border-white flex flex-col justify-center items-center text-center cursor-pointer">
                <i class="fa-solid fa-cloud-arrow-up fa-5x"></i>
                <p>Glissez et déposer pour uploader le fichier</p>
                <p>Ou</p>
                <p>Charger un fichier</p>
            </div>
            <input type="file" id="fileInputGrid" multiple hidden>
            <div id="file-list-grid" class='font-bold ml-8'></div>

       `;
        importContainer.insertAdjacentHTML('beforeend', html);

        const dropAreaGrid = document.getElementById("drop-area-grid");
        const fileInputGrid = document.getElementById("fileInputGrid");
        const fileListGrid = document.getElementById("file-list-grid");

        dropAreaGrid.addEventListener("dragover", function (e) {
            e.preventDefault();
            dropAreaGrid.style.backgroundColor = "#e9ecef";
        });

        dropAreaGrid.addEventListener("dragleave", function (e) {
            dropAreaGrid.style.backgroundColor = "#f8f9fa";
        });

        dropAreaGrid.addEventListener("drop", function (e) {
            e.preventDefault();
            dropAreaGrid.style.backgroundColor = "#f8f9fa";
            handleFilesGrid(e.dataTransfer.files);
        });

        dropAreaGrid.addEventListener("click", function () {
            fileInputGrid.click();
        });

        fileInputGrid.addEventListener("change", function (e) {
            handleFilesGrid(e.target.files);
        });
        function handleFilesGrid(files) {
            let promises = [];
            for (let file of files) {
                let reader = new FileReader();

                let promise = new Promise((resolve, reject) => {
                    reader.onload = function (event) {
                        try {
                            let content = event.target.result;
                            let gridsDataJSON = JSON.parse(content);
                            console.log("gridsDataJSON: ", gridsDataJSON);
                            Object.entries(gridsDataJSON).forEach(([gridType, grid_data]) => {
                                // Parcourir les types de valeurs (bonus/malus)
                                Object.entries(grid_data).forEach(([type, elements]) => {
                                    if (type === 'bonus' || type === 'malus') {
                                        elements.forEach((element, index) => {
                                            let inputName = `${gridType}-${type}${index + 1}`; // Ex: "qcu_bonus1", "qcm_malus2"
                                            let inputField = document.querySelector(`input[name="${inputName}"]`);

                                            if (inputField) {
                                                gridData[gridType][type][index] = element;
                                                inputField.value = element;
                                            } else {
                                                console.warn(`Champ ${inputName} non trouvé dans le DOM.`);
                                            }
                                        });
                                    }
                                });
                            });
                            document.getElementById('gridDataHidden').value = JSON.stringify(gridData);
                            
                            let fileItem = document.createElement("div");
                            fileItem.classList.add("file-item");
                            fileItem.textContent = file.name;
                            fileListGrid.innerHTML = "";
                            fileListGrid.appendChild(fileItem);

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
    }

    function updateData() {
        
    }

    function exportGrid() {
        if (gridData.length === 0) {
            alert("Aucune valeur à exporter !");
            return;
        }

        let jsonData = JSON.stringify(gridData, null, 4);

        let blob = new Blob([jsonData], { type: "application/json" });

        let a = document.createElement("a");
        a.href = URL.createObjectURL(blob);
        a.download = "grille_evaluation.json"; // Nom du fichier
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    function saveGrid() {

    }
</script>