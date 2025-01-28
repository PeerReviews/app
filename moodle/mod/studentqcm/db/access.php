<?php
defined('MOODLE_INTERNAL') || die();

// Le tableau des capacités pour le module studentqcm
$capabilities = array(

    // Définir la capacité pour ajouter une instance de ce module
    'mod/studentqcm:addinstance' => array(
        'captype' => 'write',    // Permet d'écrire (par exemple, créer une instance)
        'contextlevel' => CONTEXT_MODULE,
        'archetypes' => array(
            'teacher' => CAP_ALLOW,  // Les enseignants peuvent ajouter des instances
            'editingteacher' => CAP_ALLOW,  // Les enseignants peuvent ajouter des instances
            'student' => CAP_PREVENT,  // Les étudiants ne peuvent pas ajouter d'instances
        ),
    ),
);
