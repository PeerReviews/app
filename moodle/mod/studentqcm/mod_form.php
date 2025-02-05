<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_studentqcm_mod_form extends moodleform_mod
{
    public function definition()
    {
        global $CFG, $DB;

        $mform = $this->_form;

        // Nom de l'activité.
        $mform->addElement('text', 'name', get_string('name', 'mod_studentqcm'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('editor', 'intro', get_string('intro', 'mod_studentqcm'), null);
        $mform->setType('intro', PARAM_RAW);
        $mform->addRule('intro', null, 'required', null, 'client');

        // Ajout des champs de date.

        $mform->addElement('date_selector', 'start_date_1', get_string('start_date_1', 'mod_studentqcm'));
        $mform->addRule('start_date_1', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'end_date_1', get_string('end_date_1', 'mod_studentqcm'));
        $mform->addRule('end_date_1', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'end_date_tt_1', get_string('end_date_tt_1', 'mod_studentqcm'));
        $mform->addRule('end_date_tt_1', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'start_date_2', get_string('start_date_2', 'mod_studentqcm'));
        $mform->addRule('start_date_2', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'end_date_2', get_string('end_date_2', 'mod_studentqcm'));
        $mform->addRule('end_date_2', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'end_date_tt_2', get_string('end_date_tt_2', 'mod_studentqcm'));
        $mform->addRule('end_date_tt_2', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'start_date_3', get_string('start_date_3', 'mod_studentqcm'));
        $mform->addRule('start_date_3', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'end_date_3', get_string('end_date_3', 'mod_studentqcm'));
        $mform->addRule('end_date_3', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'end_date_tt_3', get_string('end_date_tt_3', 'mod_studentqcm'));
        $mform->addRule('end_date_tt_3', null, 'required', null, 'client');

        $this->standard_coursemodule_elements();

        // Ajouter les boutons de soumission.
        $this->add_action_buttons();
    }
}
