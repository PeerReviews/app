<?php

require_once(__DIR__ . '/../../config.php');

$context = context_system::instance();

$id = required_param('id', PARAM_INT);
$studentid = required_param('studentid', PARAM_INT);

$cm = get_coursemodule_from_id('studentqcm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$studentqcm = $DB->get_record('studentqcm', array('id' => $cm->instance), '*', MUST_EXIST);

$userid = $USER->id;

// Charger les noms des référentiels, compétences, sous-compétences et mots-clés
$referentiels = $DB->get_records_menu('referentiel', null, '', 'id, name');
$competencies = $DB->get_records_menu('competency', null, '', 'id, name');
$subcompetencies = $DB->get_records_menu('subcompetency', null, '', 'id, name');

$revisions = array();

if (!empty($studentid)) {
    $revisions = $DB->get_records('studentqcm_evaluation', array('userid' => $studentid, 'status' => 1));
}


$productions = $DB->get_record('studentqcm_assignedqcm', ['user_id' => $studentid], 'prod1_id, prod2_id, prod3_id');
$nbTotal_revision = 0;

if ($productions) {
    foreach ((array) $productions as $production_id) {
        if (!empty($production_id)) {
            $to_evaluate = $DB->get_records('studentqcm_question', array('userid' => $production_id, 'status' => 1));
            $nbTotal_revision += count($to_evaluate);
        }
    }
}

require_login($course, true, $cm);

$PAGE->set_url('/mod/studentqcm/show_production.php', array('id' => $id, 'prod_id' => $prod_id));
$PAGE->set_title(format_string($studentqcm->name));
$PAGE->set_heading(format_string($course->fullname));

$PAGE->requires->css(new moodle_url('/mod/studentqcm/style.css', array('v' => time())));


function generate_media_html($context, $filearea, $itemid, $file_storage) {
    $file_records = $file_storage->get_area_files($context->id, 'mod_studentqcm', $filearea, $itemid, 'sortorder', false);
    $media_html = '';

    foreach ($file_records as $file) {
        if ($file->get_filename() == '.') continue;

        $file_url = moodle_url::make_pluginfile_url($context->id, 'mod_studentqcm', $filearea, $itemid, $file->get_filepath(), $file->get_filename())->out();
        $file_extension = pathinfo($file->get_filename(), PATHINFO_EXTENSION);

        // Styles généraux pour tous les médias
        $common_classes = "cursor-pointer rounded-lg shadow";
        $overlay_classes = "absolute inset-0 flex items-center justify-center bg-black bg-opacity-40 rounded-lg opacity-0 hover:opacity-100 transition-opacity duration-300 cursor-pointer";
        $icon_classes = "text-white text-2xl";

        // Images
        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $media_html .= "
                <div class='relative block w-[40px] h-[40px]'>
                    <img src='{$file_url}' alt='{$file->get_filename()}' class='$common_classes w-full h-full object-cover' onclick='openMediaModal(\"{$file_url}\")' />
                    <div class='$overlay_classes' onclick='openMediaModal(\"{$file_url}\")'>
                        <i class='fas fa-search $icon_classes'></i>
                    </div>
                </div>";
        }
        // Vidéos
        elseif (in_array($file_extension, ['mp4', 'webm', 'ogg'])) {
            $media_html .= "
                <div class='relative block w-[40px] h-[40px]' onclick='openMediaModal(\"{$file_url}\")'>
                    <div class='$common_classes bg-gray-800 flex items-center justify-center w-full h-full'>
                        <i class='fas fa-play text-white text-xl'></i>
                    </div>
                    <div class='$overlay_classes'>
                        <i class='fas fa-play-circle $icon_classes'></i>
                    </div>
                </div>";
        }
        // Audios
        elseif (in_array($file_extension, ['mp3', 'wav', 'ogg'])) {
            $media_html .= "
                <div class='relative block w-[40px] h-[40px]' onclick='openMediaModal(\"{$file_url}\")'>
                    <div class='$common_classes bg-gray-700 flex items-center justify-center w-full h-full'>
                        <i class='fas fa-music text-white text-xl'></i>
                    </div>
                    <div class='$overlay_classes'>
                        <i class='fas fa-play-circle $icon_classes'></i>
                    </div>
                </div>";
        }
    }

    return $media_html;
}

echo $OUTPUT->header();

echo "<div class='mx-auto'>";
    echo "<p class='font-bold text-center text-3xl text-gray-600'>" . get_string('production1', 'mod_studentqcm') . "</p>";
echo "</div>";

// Boutons de navigation
echo "<div class='flex mt-8 text-lg justify-between'>";
    echo "<a href='admin_grade_gestion.php?id={$id}' class='inline-block px-4 py-2 font-semibold rounded-2xl bg-gray-200 hover:bg-gray-300 cursor-pointer text-gray-500 no-underline'>";
    echo "<i class='fas fa-arrow-left mr-2'></i>";
    echo get_string('back', 'mod_studentqcm');
    echo "</a>";
echo "</div>";


echo "<div class='flex justify-between items-center gap-4 mt-4 text-xl text-gray-700 font-semibold text-center'>";
    echo "<div class='flex-1 text-center rounded-3xl shadow-md p-4 bg-gray-50'>";
        echo "<p>" . get_string('student', 'mod_studentqcm') . " " . $studentid . "</p>";
        echo "<p class='text-gray-700'>" . get_string('nb_evaluated_revision', 'mod_studentqcm') . " : 
            <span id='nb-eval-questions'>" . count($revisions) . " / " . $nbTotal_revision . "</span>
        </p>";
    echo "</div>";    
echo "</div>";

$baseClasses = "w-8 h-8 rounded-xl text-gray-700 text-sm font-medium flex items-center justify-center cursor-pointer transition-all duration-300 transform";

if ($revisions) {
    echo "<div class='mt-4 grid grid-cols-2 gap-4'>";
    foreach ($revisions as $revision) {
        echo "<div class='bg-gray-50 p-4 rounded-lg flex flex-col h-full'>";
            echo "<p class='text-gray-500 mb-2'>{$revision->explanation}</p>";

            echo "<div class='flex items-center gap-4 mt-auto'>";
                echo "<p class='text-gray-700 font-medium'>" . get_string('attributed_note_revision', 'mod_studentqcm') . " :</p>";
                echo "<div class='flex gap-2'>";
                    for ($i = 0; $i <= 5; $i++) {
                        $selected = ($revision->grade == $i) ? 'bg-indigo-400 text-white scale-105 shadow-lg' : 'bg-gray-200 hover:bg-gray-300 hover:shadow-md';
                        
                        echo "<button type='button' class='{$baseClasses} {$selected}' data-eval-id='{$revision->id}' data-user-id='{$revision->userid}' onclick=''>";
                        echo "<span class='text-md font-semibold'>" . ($i === 0 ? "Ø" : $i) . "</span>";
                        echo "</button>";
                    }
                echo "</div>";
            echo "</div>";
        echo "</div>";
    }
    echo "</div>";
    
} else {
    echo "<p class='text-center text-lg text-gray-600'>" . get_string('revision_not_found', 'mod_studentqcm') . "</p>";
}

echo $OUTPUT->footer();

?>



