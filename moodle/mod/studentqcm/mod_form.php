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

        // Compétence : Liste des compétences selon le référentiel sélectionné
        $referentiels = $DB->get_records_menu('referentiel', null, '', 'id, name');
        $mform->addElement('select', 'referentiel_id', get_string('referentiel', 'mod_studentqcm'), $referentiels);
        $mform->addRule('referentiel_id', null, 'required', null, 'client');

        // Option pour ajouter plusieurs compétences en cascade selon le référentiel
        $mform->addElement('text', 'new_competency', get_string('newcompetency', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('new_competency', PARAM_TEXT);

        // Sous-compétence : Liste des sous-compétences selon la compétence sélectionnée
        $competency_id = $data ? $data->competency : 0; 
        $subcompetencies = $DB->get_records_menu('subcompetency', array('competency' => $competency_id), '', 'id, name');
        $mform->addElement('select', 'competency_id', get_string('competency', 'mod_studentqcm'), $subcompetencies);
        $mform->addRule('competency_id', null, 'required', null, 'client');

        // Option pour ajouter plusieurs sous-compétences selon la compétence
        $mform->addElement('text', 'new_subcompetency', get_string('newsubcompetency', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('new_subcompetency', PARAM_TEXT);

        // Mot-clé : Liste des mots-clés selon la sous-compétence
        $subcompetency_id = $data ? $data->subcompetency : 0;
        $keywords = $DB->get_records_menu('keyword', array('subcompetency' => $subcompetency_id), '', 'id, word');
        $mform->addElement('select', 'keyword_id', get_string('keywords', 'mod_studentqcm'), $keywords);
        $mform->addRule('keyword_id', null, 'required', null, 'client');

        // Option pour ajouter plusieurs mots-clés selon la sous-compétence
        $mform->addElement('text', 'new_keyword', get_string('newkeyword', 'mod_studentqcm'), array('size' => '64'));
        $mform->setType('new_keyword', PARAM_TEXT);

        // Ajout des champs de date
        for ($i = 1; $i <= 3; $i++) {
            $mform->addElement('date_selector', "start_$i", get_string("start_date_$i", 'mod_studentqcm'));
            $mform->addRule("start_$i", null, 'required', null, 'client');
            
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
        $errors = parent::validation($data, $files);

        // Création d'un référentiel si nécessaire
        if (!empty($data['new_referentiel'])) {
            $referentiel = new stdClass();
            $referentiel->name = $data['new_referentiel'];
            $DB->insert_record('referentiel', $referentiel);
            // On met à jour la sélection du référentiel avec l'ID du nouvel élément créé
            $data['referentiel_id'] = $DB->get_insertid();
        }

        // Création de compétences si nécessaire
        if (!empty($data['new_competency'])) {
            $competency = new stdClass();
            $competency->name = $data['new_competency'];
            $competency->referentiel = $data['referentiel_id'];
            $DB->insert_record('competency', $competency);
            // On met à jour la sélection de la compétence avec l'ID du nouvel élément créé
            $data['competency_id'] = $DB->get_insertid();
        }

        // Création de sous-compétences si nécessaire
        if (!empty($data['new_subcompetency'])) {
            $subcompetency = new stdClass();
            $subcompetency->name = $data['new_subcompetency'];
            $subcompetency->competency = $data['competency_id'];
            $DB->insert_record('subcompetency', $subcompetency);
            // On met à jour la sélection de la sous-compétence avec l'ID du nouvel élément créé
            $data['subcompetency_id'] = $DB->get_insertid();
        }

        // Création de mots-clés si nécessaire
        if (!empty($data['new_keyword'])) {
            $keyword = new stdClass();
            $keyword->word = $data['new_keyword'];
            $keyword->subcompetency = $data['subcompetency_id'];
            $DB->insert_record('keyword', $keyword);
            // On met à jour la sélection du mot-clé avec l'ID du nouvel élément créé
            $data['keyword_id'] = $DB->get_insertid();
        }

        return $errors;
    }
}
?>
