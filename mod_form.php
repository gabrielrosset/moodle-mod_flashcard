<?php

/** 
* This view allows checking deck states
* 
* @package mod-flashcard
* @category mod
* @author Gustav Delius
* @contributors Valery Fremaux
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
* @version Moodle 2.0
*/

/**
* Requires and includes 
*/
require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once ($CFG->libdir.'/questionlib.php');
require_once ($CFG->dirroot.'/mod/flashcard/locallib.php');

/**
* overrides moodleform for flashcard setup
*/
class mod_flashcard_mod_form extends moodleform_mod {
	
	var $currentfiles = array();

	function definition() {
		global $CFG, $COURSE, $DB;

        $mform    =& $this->_form;

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
      	$mform->setType('name', PARAM_TEXT);
      	$mform->addRule('name', null, 'required', null, 'client');

      /// Introduction.
        $this->add_intro_editor(false, get_string('summary', 'flashcard'));

        //$mform->addHelpButton($elementname, $identifier, $component, $linktext, $suppresscheck)
        //$mform->setHelpButton($elementname, $buttonargs, $suppresscheck, $function)

        //$mform->addHelpButton('summary', 'import', 'flashcard');
        //$mform->setHelpButton('summary', array('writing', 'questions', 'richtext2'), false, 'editorhelpbutton');


        $startdatearray[] = &$mform->createElement('date_time_selector', 'starttime', '');
        $startdatearray[] = &$mform->createElement('checkbox', 'starttimeenable', '');
        $mform->addGroup($startdatearray, 'startfrom', get_string('starttime', 'flashcard'), ' ', false);
        $mform->disabledIf('startfrom', 'starttimeenable');

        $enddatearray[] = &$mform->createElement('date_time_selector', 'endtime', '');
        $enddatearray[] = &$mform->createElement('checkbox', 'endtimeenable', '');
        $mform->addGroup($enddatearray, 'endfrom', get_string('endtime', 'flashcard'), ' ', false);
        $mform->disabledIf('endfrom', 'endtimeenable');

        if (!$questions = $DB->get_records_select('question', "qtype='match'", null, 'id, name')) {
            $questions = array();
        } else {
            // prepared for 1.9 questionbanck compatibility
            if (function_exists('question_has_capability_on')){

                function drop_questions($a){
                    return question_has_capability_on($a->id, 'use');
                }

                $questions = array_filter($questions, 'drop_questions');
            }
        }
        $qoptions = array();
        foreach($questions as $question){
            $qoptions[$question->id] = $question->name;
        }
        $mform->addElement('select', 'questionid', get_string('questionid', 'flashcard'), $qoptions);
		$mform->setAdvanced('questionid');
        $mform->addHelpButton('questionid', 'sourcequestion', 'flashcard');

        $mform->addElement('checkbox', 'forcereload', get_string('forcereload', 'flashcard'));
		$mform->setAdvanced('forcereload');
        $mform->addHelpButton('forcereload', 'forcereload', 'flashcard');

		/*
        $stylingtext = get_string('customisation', 'flashcard', $CFG->wwwroot."/files/index.php?id={$COURSE->id}&amp;wdir=%2Fmoddata%2Fflashcard");
        $stylingtext .= "<br/><br/><center><a href=\"$CFG->wwwroot/mod/flashcard/styles.php\" target=\"_blank\">".get_string('stylesheet', 'flashcard')."</a></center>";
        $mform->addElement('static', 'style', get_string('styling', 'flashcard'), $stylingtext);
        */

        $mediaoptions[FLASHCARD_MEDIA_TEXT] = get_string('text', 'flashcard');
        $mediaoptions[FLASHCARD_MEDIA_IMAGE] = get_string('image', 'flashcard');
        $mediaoptions[FLASHCARD_MEDIA_SOUND] = get_string('sound', 'flashcard');
        $mediaoptions[FLASHCARD_MEDIA_IMAGE_AND_SOUND] = get_string('imageplussound', 'flashcard');
        $mform->addElement('select', 'questionsmediatype', get_string('questionsmediatype', 'flashcard'), $mediaoptions);
        $mform->addHelpButton('questionsmediatype', 'mediatypes', 'flashcard');
        
        $mform->addElement('select', 'answersmediatype', get_string('answersmediatype', 'flashcard'), $mediaoptions);
        $mform->addHelpButton('answersmediatype', 'mediatypes', 'flashcard');

		$yesnooptions['0'] = get_string('no');
		$yesnooptions['1'] = get_string('yes');
        $mform->addElement('select', 'audiostart', get_string('audiostart', 'flashcard'), $yesnooptions);
        $mform->setType('audiostart', PARAM_BOOL);

        $mform->addElement('selectyesno', 'flipdeck', get_string('flipdeck', 'flashcard'));
        $mform->setType('flipdeck', PARAM_BOOL);
        $mform->addHelpButton('flipdeck', 'flipdeck', 'flashcard');

        $options['2'] = 2;
        $options['3'] = 3;
        $options['4'] = 4;
        $mform->addElement('select', 'decks', get_string('decks', 'flashcard'), $options);
        $mform->setType('decks', PARAM_INT); 
        $mform->setDefault('decks', 2);
        $mform->addHelpButton('decks', 'decks', 'flashcard');

        $mform->addElement('selectyesno', 'autodowngrade', get_string('autodowngrade', 'flashcard'));
        $mform->addHelpButton('autodowngrade', 'autodowngrade', 'flashcard');
		$mform->setAdvanced('autodowngrade');

        $mform->addElement('text', 'deck2_release', get_string('deck2_release', 'flashcard'), array('size'=>'5'));
        $mform->addHelpButton('deck2_release', 'deck_release', 'flashcard');
        $mform->setType('deck2_release', PARAM_INT);
        $mform->setDefault('deck2_release', 96);
        $mform->addRule('deck2_release', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');
		$mform->setAdvanced('deck2_release');
 
        $mform->addElement('text', 'deck3_release', get_string('deck3_release', 'flashcard'), array('size'=>'5'));
        $mform->setType('deck3_release', PARAM_INT);
        $mform->setDefault('deck3_release', 96);
        $mform->addRule('deck3_release', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck3_release', 'decks', 'eq', 2);
		$mform->setAdvanced('deck3_release');

        $mform->addElement('text', 'deck4_release', get_string('deck4_release', 'flashcard'), array('size'=>'5'));
        $mform->setType('deck4_release', PARAM_INT);
        $mform->setDefault('deck4_release', 96);
        $mform->addRule('deck4_release', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck4_release', 'decks', 'neq', 4);
		$mform->setAdvanced('deck4_release');

        $mform->addElement('text', 'deck1_delay', get_string('deck1_delay', 'flashcard'), array('size'=>'5'));
        $mform->addHelpButton('deck1_delay', 'deck_delay', 'flashcard');
        $mform->setType('deck1_delay', PARAM_INT);
        $mform->setDefault('deck1_delay', 48);
        $mform->addRule('deck1_delay', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');

        $mform->addElement('text', 'deck2_delay', get_string('deck2_delay', 'flashcard'), array('size'=>'5'));
        $mform->setType('deck2_delay', PARAM_INT);
        $mform->setDefault('deck2_delay', 96);
        $mform->addRule('deck2_delay', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');

        $mform->addElement('text', 'deck3_delay', get_string('deck3_delay', 'flashcard'), array('size'=>'5'));
        $mform->setType('deck3_delay', PARAM_INT);
        $mform->setDefault('deck3_delay', 168);
        $mform->addRule('deck3_delay', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck3_delay', 'decks', 'eq', 2);

        $mform->addElement('text', 'deck4_delay', get_string('deck4_delay', 'flashcard'), array('size'=>'5'));
        $mform->setType('deck4_delay', PARAM_INT);
        $mform->setDefault('deck4_delay', 336);
        $mform->addRule('deck4_delay', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck4_delay', 'decks', 'neq', 4);

        $mform->addElement('header', 'notifications_head', get_string('notifications', 'flashcard'));

        $mform->addElement('select', 'remindusers', get_string('remindusers', 'flashcard'), $yesnooptions);
        $mform->setType('remindusers', PARAM_BOOL);

        $mform->addElement('header', 'customfiles_head', get_string('customisationfiles', 'flashcard'));
        $mform->setAdvanced('customfiles_head');
        
        $customcardoptions = array('maxfiles' => 1, 'maxbytes' => $COURSE->maxbytes, 'accepted_types' => array('.jpg', '.png', '.gif'));
        
		$maxbytes = 100000;
        $mform->addElement('filepicker', 'custombackfileid', get_string('cardfront', 'flashcard'), null, $customcardoptions);
        $mform->setAdvanced('custombackfileid');
        $mform->addElement('filepicker', 'customfrontfileid', get_string('cardback', 'flashcard'), null, $customcardoptions);
        $mform->setAdvanced('customfrontfileid');
        $mform->addElement('filepicker', 'customemptyfileid', get_string('emptydeck', 'flashcard'), null, $customcardoptions);
        $mform->setAdvanced('customemptyfileid');
        $mform->addElement('filepicker', 'customreviewfileid', get_string('reviewback', 'flashcard'), null, $customcardoptions);
        $mform->setAdvanced('customreviewfileid');
        $mform->addElement('filepicker', 'customreviewedfileid', get_string('reviewedback', 'flashcard'), null, $customcardoptions);
        $mform->setAdvanced('customreviewedfileid');
        $mform->addElement('filepicker', 'customreviewemptyfileid', get_string('reviewedempty', 'flashcard'), null, $customcardoptions);
        $mform->setAdvanced('customreviewemptyfileid');

        $mform->addElement('textarea', 'extracss', get_string('extracss', 'flashcard'), array('cols'=>'60', 'rows' => 15));
        $mform->setAdvanced('extracss');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
	}

      function add_completion_rules() {
        $mform =& $this->_form;

        $group = array();
        $group[] =& $mform->createElement('checkbox', 'completionallviewedenabled', '', get_string('completionallviewed', 'flashcard'));
        $group[] =& $mform->createElement('text', 'completionallviewed', '', array('size' => 3));
        $mform->setType('completionallviewed', PARAM_INT);
        $mform->addGroup($group, 'completionallviewedgroup', get_string('completionallviewedgroup', 'flashcard'), array(' '), false);
        $mform->disabledIf('completionallviewedgroup', 'completionallgoodenabled', 'checked');

        $group = array();
        $group[] =& $mform->createElement('checkbox', 'completionallgoodenabled', '', get_string('completionallgoodenabled', 'flashcard'));
        $mform->addGroup($group, 'completionallgoodgroup', get_string('completionallgoodgroup', 'flashcard'), array(' '), false);

        return array('completionallviewedgroup','completionallgoodgroup');
    }

    function completion_rule_enabled($data) {
        return (!empty($data['completionallviewedenabled']) && $data['completionallviewed']!=0) ||
            (!empty($data['completionallgood']));
    }

    function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);

        // Set up the completion checkboxes which aren't part of standard data.
        // We also make the default value (if you turn on the checkbox) for those
        // numbers to be 1, this will not apply unless checkbox is ticked.
        $default_values['completionallviewedenabled'] =
            !empty($default_values['completionallviewed']) ? 1 : 0;
        if (empty($default_values['completionallviewed'])) {
            $default_values['completionallviewed'] = 999;
        }
        $default_values['completionallgoodenabled'] = !empty($default_values['completionallgood']) ? 1 : 0;
    }

	
	function set_data($data){
		
		if ($data->coursemodule){
			$context = context_module::instance($data->coursemodule);
			 
			$draftitemid = file_get_submitted_draft_itemid('customfront');		 
			$maxbytes = 100000;
			file_prepare_draft_area($draftitemid, $context->id, 'mod_flashcard', 'customfront', 0, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));		 
			$data->customfrontfileid = $draftitemid;

			$draftitemid = file_get_submitted_draft_itemid('customback');		 
			$maxbytes = 100000;
			file_prepare_draft_area($draftitemid, $context->id, 'mod_flashcard', 'customback', 0, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));		 
			$data->custombackfileid = $draftitemid;

			$draftitemid = file_get_submitted_draft_itemid('customempty');		 
			$maxbytes = 100000;
			file_prepare_draft_area($draftitemid, $context->id, 'mod_flashcard', 'customempty', 0, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));		 
			$data->customemptyfileid = $draftitemid;

			$draftitemid = file_get_submitted_draft_itemid('customreview');		 
			$maxbytes = 100000;
			file_prepare_draft_area($draftitemid, $context->id, 'mod_flashcard', 'customreview', 0, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));		 
			$data->customreviewfileid = $draftitemid;

			$draftitemid = file_get_submitted_draft_itemid('customreview');		 
			$maxbytes = 100000;
			file_prepare_draft_area($draftitemid, $context->id, 'mod_flashcard', 'customreviewed', 0, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));		 
			$data->customreviewedfileid = $draftitemid;

			$draftitemid = file_get_submitted_draft_itemid('customreviewempty');		 
			$maxbytes = 100000;
			file_prepare_draft_area($draftitemid, $context->id, 'mod_flashcard', 'customreviewempty', 0, array('subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 1));		 
			$data->customreviewemptyfileid = $draftitemid;
		}
		
		if (empty($data->extracss)){
			$data->extracss = '
/* panel div for question */
.flashcard-question{
}
/* panel div for answer */
.flashcard-answer{
}
			';
		}
		
		parent::set_data($data);
	}
	
	function get_data(){
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        
        // Turn off completion settings if the checkboxes aren't ticked
        if (!empty($data->completionunlocked)) {

            // weird effect of form resubmission. but why ?
        	$data->completionallviewedenabled = @$_POST['completionallviewedenabled'];
        	$data->completionallviewed = @$_POST['completionallviewed'];
        	$data->completionallgoodenabled = @$_POST['completionallgoodenabled'];
        	
            $autocompletion = !empty($data->completion) && $data->completion == COMPLETION_TRACKING_AUTOMATIC;
            if (empty($data->completionallviewedenabled) || !$autocompletion) {
                $data->completionallviewed = 0;
            } else {
                $data->completionallviewed = 999;
            }
            if (empty($data->completionallgoodenabled) || !$autocompletion) {
                $data->completionallgood = 0;
            } else {
                $data->completionallgood = 1;
            }
        }
        return $data;
	}
	
	function validation($data, $files = array()) {
	    $errors = array();

        if ($data['starttime'] > $data['endtime']){
            $errors['endfrom'] = get_string('mustbehigherthanstart', 'flashcard');
        }
	    
	    if ($data['decks'] >= 2){
	        if ($data['deck1_delay'] > $data['deck2_delay']) {
	            $errors['deck2_delay'] = get_string('mustbegreaterthanabove');
	        }
	    }
	    if ($data['decks'] >= 3){
	        if ($data['deck2_delay'] > $data['deck3_delay']) {
	            $errors['deck3_delay'] = get_string('mustbegreaterthanabove');
	        }
	    }
	    if ($data['decks'] >= 4){
	        if ($data['deck3_delay'] > $data['deck4_delay']) {
	            $errors['deck4_delay'] = get_string('mustbegreaterthanabove');
	        }
	    }
	    return $errors;
	}

}
