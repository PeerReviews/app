<?php

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_externalpage(
        'local_mass_enroll',
        get_string('mass_enroll', 'local_mass_enroll'),
        new moodle_url('/local/mass_enroll/massenrol.php'),
        'moodle/site:config'
    ));
}
