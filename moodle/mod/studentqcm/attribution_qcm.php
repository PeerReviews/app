<?php
try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 1️⃣ Récupérer tous les étudiants
    $stmt = $pdo->query("SELECT id FROM mdl_user");
    $students = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($students) < 2) {
        die("Il faut au moins 2 étudiants pour faire l'attribution !");
    }

    // 2️⃣ Récupérer tous les QCM par étudiant
    $stmt = $pdo->query("SELECT id, userid FROM mdl_studentqcm_question");
    $qcms = $stmt->fetchAll();

    // Préparer une liste de QCM à distribuer
    $qcmDispo = [];
    foreach ($qcms as $qcm) {
        $qcmDispo[$qcm['userid']][] = $qcm['id'];
    }

    // Tableau pour suivre combien de QCM chaque étudiant a reçu
    $qcmAttribues = array_fill_keys($students, []);

    // 3️⃣ Distribuer les QCM équitablement
    foreach ($qcmDispo as $creatorId => $qcmList) {
        shuffle($qcmList); // Mélanger les QCM pour mieux répartir

        foreach ($qcmList as $qcmId) {
            // Trouver un étudiant qui n'est pas le créateur et qui a reçu le moins de QCM
            $candidats = array_diff($students, [$creatorId]); // Exclure le créateur
            usort($candidats, function($a, $b) use ($qcmAttribues) {
                return count($qcmAttribues[$a]) - count($qcmAttribues[$b]);
            });

            $assignedStudent = array_shift($candidats); // Prendre celui qui a le moins de QCM

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
