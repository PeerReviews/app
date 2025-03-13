<?php
defined('MOODLE_INTERNAL') || die();

$capabilities = array(

    'mod/peerreview:addinstance' => array(
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
    'mod/peerreview:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'student' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
        ),
    ),
);