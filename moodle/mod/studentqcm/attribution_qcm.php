<?php
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $stmt = $pdo->query("SELECT id FROM mdl_user");
    $students = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($students) < 2) {
        die("Il faut au moins 2 étudiants pour faire l'attribution !");
    }

    $stmt = $pdo->query("SELECT id, userid FROM mdl_studentqcm_question");
    $qcms = $stmt->fetchAll();

    // Préparer une liste de QCM à distribuer
    $qcmDispo = [];
    foreach ($qcms as $qcm) {
        $qcmDispo[$qcm['userid']][] = $qcm['id'];
    }

    $qcmAttribues = array_fill_keys($students, []);

    foreach ($qcmDispo as $creatorId => $qcmList) {
        shuffle($qcmList);

        foreach ($qcmList as $qcmId) {
            // Trouver un étudiant qui n'est pas le créateur et qui a reçu le moins de QCM
            $candidats = array_diff($students, [$creatorId]); 
            usort($candidats, function($a, $b) use ($qcmAttribues) {
                return count($qcmAttribues[$a]) - count($qcmAttribues[$b]);
            });

            $assignedStudent = array_shift($candidats); 

            // Assigner le QCM
            $qcmAttribues[$assignedStudent][] = $qcmId;

            // Insérer dans la nouvelle table
            $stmt = $pdo->prepare("INSERT INTO mdl_studentqcm_assignedqcm (userId, qcm_id) VALUES (:userId, :qcmId)");
            $stmt->execute([
            'userId' => $assignedStudent,
            'qcmId' => $qcmId
        ]);
        }
    }

    echo "Tous les QCM ont été attribués équitablement !\n";

} catch (PDOException $e) {
    die("Erreur SQL : " . $e->getMessage());
}
?>
