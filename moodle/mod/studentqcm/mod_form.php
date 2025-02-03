<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_studentqcm_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        // Informations principales du référentiel.
        $mform->addElement('html', '<h2>' . get_string('info_referentiel_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('text', 'name_referentiel', get_string('name_referentiel', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('name_referentiel', PARAM_TEXT);
        $mform->addRule('name_referentiel', null, 'required', null, 'client');
        
        $mform->addElement('editor', 'intro', get_string('intro', 'mod_studentqcm'), null);
        $mform->setType('intro', PARAM_RAW);
        $mform->addRule('intro', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'date_start_referentiel', get_string('date_start_referentiel', 'mod_studentqcm'));
        $mform->addRule('date_start_referentiel', null, 'required', null, 'client');

        $mform->addElement('date_selector', 'date_end_referentiel', get_string('date_end_referentiel', 'mod_studentqcm'));
        $mform->addRule('date_end_referentiel', null, 'required', null, 'client');

        // Ajout compétences
        $mform->addElement('html', '<h2>' . get_string('competences_title', 'mod_studentqcm') . '</h2>');
        // Ajout du bouton "Ajouter competences"
        $mform->addElement('html', '<h3>' . get_string('info_competence', 'mod_studentqcm') . '</h3>');

        $mform->addElement('text', 'name_competence', get_string('name_competence', 'mod_studentqcm'), null);
        $mform->setType('name_competence', PARAM_TEXT);
        $mform->addRule('name_competence', null, 'required', null, 'client');
        //Ajout bouton "Valider"

        // Ajout sous-compétences

        $mform->addElement('html', '<h2>' . get_string('subcompetences_title', 'mod_studentqcm') . '</h2>');
        // Ajout du bouton "Ajouter sous-competences"
        $mform->addElement('html', '<h3>' . get_string('info_subcompetence', 'mod_studentqcm') . '</h3>');

        $mform->addElement('text', 'name_subcompetence', get_string('name_subcompetence', 'mod_studentqcm'), null);
        $mform->setType('name_subcompetence', PARAM_TEXT);
        $mform->addRule('name_subcompetence', null, 'required', null, 'client');

        $mform->addElement('text', 'competence_associated', get_string('competence_associated', 'mod_studentqcm'), null);
        $mform->setType('competence_associated', PARAM_TEXT);
        $mform->addRule('competence_associated', null, 'required', null, 'client');
        //Ajout bouton "Valider"

        // Ajout mot-clefs

        $mform->addElement('html', '<h2>' . get_string('Choix dates des phases:', 'mod_studentqcm') . '</h2>');
        // Ajout du bouton "Ajouter mot-clefs"
        $mform->addElement('html', '<h3>' . get_string('info_keyword', 'mod_studentqcm') . '</h3>');

        $mform->addElement('text', 'name_keyword', get_string('name_keyword', 'mod_studentqcm'), null);
        $mform->setType('name_keyword', PARAM_TEXT);
        $mform->addRule('name_keyword', null, 'required', null, 'client');

        $mform->addElement('text', 'subcompetence_associated', get_string('subcompetence_associated', 'mod_studentqcm'), null);
        $mform->setType('subcompetence_associated', PARAM_TEXT);
        $mform->addRule('subcompetence_associated', null, 'required', null, 'client');
        //Ajout bouton "Valider"

        // Ajout des champs de date.
        $mform->addElement('html', '<h2>' . get_string('keyword_title', 'mod_studentqcm') . '</h2>');

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

        // Choix étudiants

        $mform->addElement('html', '<h2>' . get_string('choice_etu_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('static', 'import_etu', get_string('import_etu', 'mod_studentqcm'), null);

        $mform->addElement('static', 'selected_etu', get_string('selected_etu', 'mod_studentqcm'), null);
        // Ajout du bouton "Ajouter un étudiant"
        $mform->addElement('html', '<h3>' . get_string('info_etu', 'mod_studentqcm') . '</h3>');

        $mform->addElement('text', 'surname', get_string('surname', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('surname', PARAM_TEXT);
        $mform->addRule('surname', null, 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('name', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'mail', get_string('mail', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('mail', PARAM_TEXT);
        $mform->addRule('mail', null, 'required', null, 'client');
        //Ajout bouton "Valider"

        // Choix étudiants tiers-temps

        $mform->addElement('html', '<h2>' . get_string('choice_etu_tt_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('static', 'import_etu', get_string('import_etu', 'mod_studentqcm'), null);

        $mform->addElement('static', 'selected_etu', get_string('selected_etu', 'mod_studentqcm'), null);
        // Ajout du bouton "Ajouter un étudiant"
        $mform->addElement('html', '<h3>' . get_string('info_etu', 'mod_studentqcm') . '</h3>');

        $mform->addElement('text', 'surname', get_string('surname', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('surname', PARAM_TEXT);
        $mform->addRule('surname', null, 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('name', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'mail', get_string('mail', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('mail', PARAM_TEXT);
        $mform->addRule('mail', null, 'required', null, 'client');
        //Ajout bouton "Valider"

        // Choix prof

        $mform->addElement('html', '<h2>' . get_string('choice_prof_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('static', 'import_prof', get_string('import_prof', 'mod_studentqcm'), null);

        $mform->addElement('static', 'selected_prof', get_string('selected_prof', 'mod_studentqcm'), null);
        // Ajout du bouton "Ajouter un étudiant"
        $mform->addElement('html', '<h3>' . get_string('info_prof', 'mod_studentqcm') . '</h3>');

        $mform->addElement('text', 'surname', get_string('surname', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('surname', PARAM_TEXT);
        $mform->addRule('surname', null, 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('name', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('text', 'mail', get_string('mail', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('mail', PARAM_TEXT);
        $mform->addRule('mail', null, 'required', null, 'client');
        //Ajout bouton "Valider"

        // Choix types éval

        $options = ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10', '11' => '11', '12' => '12', '13' => '13', '14' => '14', '15' => '15', '16' => '16', '17' => '17'];

        $mform->addElement('html', '<h2>' . get_string('type_eval_title', 'mod_studentqcm') . '</h2>');

        $mform->addElement('select', 'choix', get_string('nb_qcm', 'mod_studentqcm'), $options);

        $mform->addElement('select', 'choix', get_string('nb_qcu', 'mod_studentqcm'), $options);
        
        $mform->addElement('select', 'choix', get_string('nb_tcs', 'mod_studentqcm'), $options);
        
        $mform->addElement('select', 'choix', get_string('nb_pop', 'mod_studentqcm'), $options);
        
        $this->standard_coursemodule_elements();

        // Ajouter les boutons de soumission.
        $this->add_action_buttons();
    }
}