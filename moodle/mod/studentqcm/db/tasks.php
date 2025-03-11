<?php
defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'mod_studentqcm\task\attribution_student_task',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '0',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*'
    ],
    [
        'classname' => 'mod_studentqcm\task\attribution_teacher_task',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '0',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*'
    ],
    [
        'classname' => 'mod_studentqcm\task\set_grade_task',
        'blocking'  => 0,
        'minute'    => '0',
        'hour'      => '0',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*'
    ]
];
