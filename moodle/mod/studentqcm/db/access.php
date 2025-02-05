<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = [

    'mod/studentqcm:addinstance' => [
        'riskbitmask' => RISK_XSS,

        'captype' => 'write',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes' => [
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'student' => CAP_PREVENT,
        ],
        'clonepermissionsfrom' => 'moodle/course:manageactivities'
    ],
    'mod/studentqcm:view' => [
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => [
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ],
    ],
];
