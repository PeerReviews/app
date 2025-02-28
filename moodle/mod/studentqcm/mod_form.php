<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/course/moodleform_mod.php');

class mod_studentqcm_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $DB;

        $mform = $this->_form;

        // Vérification si nous sommes dans un mode création ou édition
        $data = $this->current ? $this->current : null;

        // Nom de l'activité
        $mform->addElement('text', 'name', get_string('name', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('editor', 'intro', get_string('intro', 'mod_studentqcm'), null);
        $mform->setType('intro', PARAM_RAW);

        // Ajout du champ pour le référentiel
        $mform->addElement('text', 'new_referentiel', get_string('newreferentiel', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('new_referentiel', PARAM_TEXT);

        // Option pour ajouter plusieurs compétences en cascade selon le référentiel
        $mform->addElement('text', 'new_competency', get_string('newcompetency', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('new_competency', PARAM_TEXT);

        // Option pour ajouter plusieurs sous-compétences selon la compétence
        $mform->addElement('text', 'new_subcompetency', get_string('newsubcompetency', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('new_subcompetency', PARAM_TEXT);

        // Option pour ajouter plusieurs mots-clés selon la sous-compétence
        $mform->addElement('text', 'new_keyword', get_string('newkeyword', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('new_keyword', PARAM_TEXT);

        // Ajout des champs de date
        for ($i = 1; $i <= 3; $i++) {
            $mform->addElement('date_selector', "start_date_$i", get_string("start_date_$i", 'mod_studentqcm'));
            $mform->addRule("start_date_$i", null, 'required', null, 'client');
            
            $mform->addElement('date_selector', "end_date_$i", get_string("end_date_$i", 'mod_studentqcm'));
            $mform->addRule("end_date_$i", null, 'required', null, 'client');
            
            $mform->addElement('date_selector', "end_date_tt_$i", get_string("end_date_tt_$i", 'mod_studentqcm'));
            $mform->addRule("end_date_tt_$i", null, 'required', null, 'client');
        }

        // Ajout des éléments de base du cours (standard course module elements)
        $this->standard_coursemodule_elements();

        // Boutons d'action (enregistrer, annuler)
        $this->add_action_buttons();
    }

    // Fonction pour gérer la création d'un nouveau référentiel, compétence, sous-compétence et mot-clé
    public function validation($data, $files) {

        global $DB;
        $errors = parent::validation($data, $files);

        // Création d'un référentiel si nécessaire
        if (!empty($data['new_referentiel'])) {
            $referentiel = new stdClass();
            $referentiel->name = 'Cardiaque';
            $data['referentiel_id'] =$DB->insert_record('referentiel', $referentiel);
        }

        // Création de compétences si nécessaire
        if (!empty($data['new_competency'])) {
            $competency = new stdClass();
            $competency->name = $data['new_competency'];
            $competency->referentiel = $data['referentiel_id'];
            $data['competency_id'] = $DB->insert_record('competency', $competency);
            
        }

        // Création de sous-compétences si nécessaire
        if (!empty($data['new_subcompetency'])) {
            $subcompetency = new stdClass();
            $subcompetency->name = $data['new_subcompetency'];
            $subcompetency->competency = $data['competency_id'];
            $data['subcompetency_id'] = $DB->insert_record('subcompetency', $subcompetency);
        }

        // Création de mots-clés si nécessaire
        if (!empty($data['new_keyword'])) {
            $keyword = new stdClass();
            $keyword->word = $data['new_keyword'];
            $keyword->subcompetency = $data['subcompetency_id'];
            $data['keyword_id'] = $DB->insert_record('keyword', $keyword);
        }

        return $errors;
    }
}
?>
