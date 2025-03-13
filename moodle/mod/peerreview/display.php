<?php
require_once('config.php'); // Charger Moodle
global $DB;

$fileid = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($fileid <= 0) {
    die("Fichier introuvable !");
}

$file = $DB->get_record('files', ['id' => $fileid]);

if (!$file) {
    die("Fichier introuvable en base !");
}

$folder1 = substr($file->contenthash, 0, 2);
$folder2 = substr($file->contenthash, 2, 2);
$filepath = "/var/www/moodledata/filedir/$folder1/$folder2/{$file->contenthash}";

if (!file_exists($filepath)) {
    die("Le fichier n'existe pas sur le serveur !");
}

header("Content-Type: " . $file->mimetype);
header("Content-Length: " . $file->filesize);
readfile($filepath);
exit;
?>
