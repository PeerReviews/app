<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'mod/studentqcm:addinstance' => array(
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => array(
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'student' => CAP_PREVENT,
        ),
        'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ),
);
