<?php
/*************************************************************************
 *  Copyright notice
 *
 *  © 2010-2011 Sven-S. Porst, SUB Göttingen <porst@sub.uni-goettingen.de>
 *  All rights reserved
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  This copyright notice MUST APPEAR in all copies of the script.
 *************************************************************************/


/**
 * Pazpar2neuerwerbungenController.php
 *
 * Main controller for pazpar2 Neuerwerbungen plug-in,
 * of the pazpar2 Extension.
 *
 * @author Sven-S. Porst <porst@sub-uni-goettingen.de>
 */




/**
 * Controller for the pazpar2 Neuerwerbungen package.
 */
class Tx_Pazpar2_Controller_Pazpar2neuerwerbungenController extends Tx_Pazpar2_Controller_Pazpar2Controller {

	/**
	 * Model object used for handling the parameters.
	 * @var Tx_Pazpar2neuerwerbungen_Domain_Model_Pazpar2neuerwerbungen
	 */
	protected $pz2Neuerwerbungen;


	
	/**
	 * defaultSettings: Return array with default settings.
	 * Add own default settings to those set by the superclass.
	 *
	 * @return Array
	 */
	protected function defaultSettings () {
		$defaults = parent::defaultSettings();
		$defaults['pz2-neuerwerbungenJSPath'] = t3lib_extMgm::siteRelPath('pazpar2') . 'Resources/Public/pz2-neuerwerbungen.js';
		$defaults['pz2-neuerwerbungenCSSPath'] = t3lib_extMgm::siteRelPath('pazpar2') . 'Resources/Public/pz2-neuerwerbungen.css';
		$defaults['subjects'] = '';

		return $defaults;
	}


	
	/**
	 * Initialiser.
	 * 
	 * Initialises parent class and sets up model object.
	 *
	 * @return void
	 */
	public function initializeAction() {
		parent::initializeAction();
		
		$this->pz2Neuerwerbungen = t3lib_div::makeInstance('Tx_Pazpar2_Domain_Model_Pazpar2neuerwerbungen');
		$this->pz2Neuerwerbungen->setMonths($this->monthsArray());
	}
	
	
	
	/**
	 * Index: Make superclass insert <script> and <link> tags into <head>.
	 * Load subjects, set up the query string, run the superclass’ action 
	 *  (which does the relevant pazpar2 queries if necessary) and assign the 
	 *  results to the view.
	 *
	 * @return void
	 */
	public function indexAction () {
		$this->setupSubjects();
		$this->setupQueryString();
		
		parent::indexAction();

		$this->view->assign('pazpar2neuerwerbungen', $this->pz2Neuerwerbungen);
	}


	
	/**
	 * Get the stored subjects array and add information about the selected ones
	 *  from our arguments to it and set the model object up to use it.
	 * 
	 * If all child elements in a subject group are selected, also select the
	 *	group itself.
	 * 
	 * If a subject group is selected, also select all the subjects contained in
	 *  it. (If a subject group is not selected, _don't_ deselect all the
	 *  children. Imperfect but probably the most reasonable thing to be done in
	 *  a non-interactive setup like this one.)
	 * 
	 * @return void
	 */
	private function setupSubjects () {
		$subjects = $this->getSubjectsArray();

		// Figure out selected subjects from arguments. Subject argument names
		// are "pz2subject-x-y" where x is the index of the subject group
		// and -y is optional with y being the index of the subject inside its
		// group.
		$arguments = $this->request->getArguments();
		foreach ($arguments as $argumentName => $argument) {
			if ($argument != '' && strpos($argumentName, 'pz2subject-') == 0) {
				$nameParts = explode('-', $argumentName);
				if (count($nameParts) >= 2) {
					$groupIndex = intval($nameParts[1]);
					
					if (count($nameParts) == 3) {
						$subjectIndex = intval($nameParts[2]);
						$subjects[$groupIndex]['subjects'][$subjectIndex]['selected'] = True;
					}
					else {
						$subjects[$groupIndex]['selected'] = True;
					}
				}
			}
		}
		
		// Turn on the selection for the group if all containing subjects are selected
		foreach ($subjects as &$group) {
			$isSelected = True;
			foreach ($group['subjects'] as &$subject) {
				$isSelected &= $subject['selected'];
			}
			if ($isSelected) {
				$group['selected'] = True;
			}
		}
		
		// Turn on the selection for all included subjects if the containing
		// group is selected.
		foreach ($subjects as &$group) {
			if ($group['selected']) {
				foreach ($group['subjects'] as &$subject) {
					$subject['selected'] = True;
				}
			}
		}

		$this->pz2Neuerwerbungen->setSubjects($subjects);
	}
	
	
	
	/**
	 * Get the query string for the current setup and set it in our Query
	 *  model object.
	 * 
	 * @return void
	 */
	private function setupQueryString () {
		$queryString = $this->buildSearchQueryWithEqualsAndWildcard();
		$this->query->setQueryString($queryString);
	}
	
	
	
	/**
	 * Return the array of all GOKs selected in the form, taking into account
	 *  group checkboxes.
	 * 
	 * @param String $wildcard appended to each extracted GOK
	 * @return array of GOK strings
	 */
	private function selectedGOKsInFormWithWildcard($wildcard) {
		$GOKs = Array();
		
		$subjects = $this->pz2Neuerwerbungen->getSubjects();
		foreach ($subjects as $group) {
			if ($group['selected'] && $group['GOKs']) {
				$this->addSearchTermsToList($group['GOKs'], $GOKs, $wildcard);
			}
			else {
				foreach ($group['subjects'] as $subject) {
					if ($subject['selected']) {
						$this->addSearchTermsToList($subject['GOKs'], $GOKs, $wildcard);
					}
				}
			}
		}
		
		return $GOKs;
	}
	
	
	
	/**
	 * Builds a query string using the selected GOKs in the form.
	 * The strings used for equals assignment and wildcard can be configured
	 *  to yield string that can be used for both Pica- and CCL-style queries.
	 * Null is returned when there are no GOKs to search for.
	 * 
	 * @param string $equals [defaults to '=']
	 * @param string $wildcard [defaults to '']
	 * @return string 
	 */
	private function buildSearchQueryWithEqualsAndWildcard ($equals = '=', $wildcard = '') {
		$queryString = Null;
		
		$GOKs = $this->selectedGOKsInFormWithWildcard($wildcard);
		if (count($GOKs) > 0) {
			$LKLQueryString = $this->oredSearchQueries($GOKs, 'lkl', $equals);
			$dates = Array();
			$arguments = $this->request->getArguments();
			$monthsArray = explode(',', $arguments['months']);
			$this->addSearchTermsToList($monthsArray, $dates, $wildcard);
			$DTMQueryString = $this->oredSearchQueries($dates, 'dtm', $equals);
			
			$queryString = $LKLQueryString . ' and ' . $DTMQueryString;
  		}
		
		return $queryString;
	}
	
	
	
	/**
	 * Helper function for preparing search queries.
	 * 
	 * @param Array $queryTerms strings, each of which will be a sub-query
	 * @param string $key search key the query is made for
	 * @param string $equals string used to separate the key and the query term
	 * @return string query
	 */
	private function oredSearchQueries ($queryTerms, $key, $equals) {
		$query = '(' . $key . $equals . implode(' or ' . $key . $equals, $queryTerms) . ')';
		return $query;
	}
	
	
	
	/**
	 * Helper function adding the elements of an array to a given array,
	 *  potentially appending a wildcard to each of them in the process.
	 * 
	 * @param Array $searchTerms strings to be added to the list
	 * @param type $list Array the terms are added to
	 * @param type $wildcard string that is appended to each component before adding it to the list
	 */
	private function addSearchTermsToList ($searchTerms, &$list, $wildcard) {
		foreach($searchTerms as $term) {
			if ($term != '') {
				$list[] = $term . $wildcard;
			}
		}
	}
	
	
	
	/**
	 * reduceMonth: Assume the passed references to month and year numbers
	 *	indicate a month; reduce them to indicate the previous month.
	 *
	 * @param $month reference to month number
	 * @param $year reference to year number
	 * @return void
	 */
	private function reduceMonth (&$month, &$year) {
		if ($month == 1) {
			$month = 12;
			$year--;
		}
		else {
			$month--;
		}
	}



	/**
	 * Return search string for Pica format of the given month: YYYYMM
	 *
	 * @param $month month number
	 * @param $year year number
	 * @return string the given month in YYYYMM format
	 */
	private function picaSearchStringForMonth ($month, $year) {
		$leadingZero = '';
		
		if ($month < 10) {
			$leadingZero = '0';
		}
		
		return $year . $leadingZero . $month;
	}



	/**
	 * Returns array of months preceding the current one.
	 *	* Keys are of the form YYYYMM.
	 *	* Values are localised names of the months followed by the year.
	 *		Localised '(incomplete)' is appended to the name of the current month.
	 *
	 * @param $numberOfMonths (default = 13)
	 * @return Array
	 */
	private function monthsArray ($numberOfMonths = 13) {
		$months = array();
		$year = date('Y');
		$month = date('n');
		
		for ($i = 1; $i <= $numberOfMonths; $i++) {
			$searchString = $this->picaSearchStringForMonth($month, $year);
			
			/* make sure the text encoding in the locale_all setting matches the encoding 
					of the page, otherwise umlauts in month names may appear broken */  
			$monthName = strftime('%B', mktime(0, 0, 0, $month, 1, 2010));
			$displayString = $monthName . ' ' . $year;

			if ($i == 1) {
				$displayString .= ' (' . Tx_Extbase_Utility_Localization::translate('unvollständig', 'pazpar2') . ')';
			}

			$months[$searchString] = $displayString;

			$this->reduceMonth($month, $year);
		}
		
		return $months;
	}



	/**
	 * Return array of subjects.
	 *	conf['subjects'] determines the name of the data file in Configuration/Subjects.
	 *  Running the file sets the variable $subjectGroups to the array of subjects.	 *
	 *  Its form should be
	 *		* Array [subject groups]
	 *			* Array [subject group, associative]
	 *				* name => string - name of subject group [required]
	 *				* GOKs => Array [optional]
	 *					* string that is a truncated GOK notation
	 *				* subjects => Array [required, subjects]
	 *					* Array [subject, associative]
	 *						* name => string - name of subject group [required]
	 *						* GOKs => Array [required]
	 *							* string that is a truncated GOK notation
	 *						* inline => boolean - displayed in one line with other items?
	 *							[optional, defaults to false]
	 *						* break => boolean - insert <br> before current element?
	 *							[optional, should only be used with inline => true, defaults to false]
	 *
	 * If the GOKs field of a subject group is not specified, create it by taking
	 *	the union of the GOKs arrays of all its subjects.
	 *
	 * @return Array subjects to be displayed
	 */
	private function getSubjectsArray () {
		$subjectsFile = 'Configuration/Subjects/' . $this->conf['subjects'] . '.php';
		require_once(t3lib_extMgm::siteRelPath('pazpar2') . $subjectsFile);

		foreach ($subjectGroups as &$subjectGroup) {
			if (!$subjectGroup['GOKs']) {
				$GOKs = array();
				foreach ($subjectGroup['subjects'] as $subject) {
					foreach ($subject['GOKs'] as $GOK) {
						$GOKs[] = $GOK;
					}
				}
				$subjectGroup['GOKs'] = $GOKs;
			}
		}

		return $subjectGroups;
	}



	/**
	 * Inserts headers into page: first general ones by the superclass,
	 *	then our own.
	 *
	 * @return void
	 */
	protected function addResourcesToHead () {
		parent::addResourcesToHead();

		// Add pz2-neuerwerbungen.css to <head>.
		$cssTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('link');
		$cssTag->addAttribute('rel', 'stylesheet');
		$cssTag->addAttribute('type', 'text/css');
		$cssTag->addAttribute('href', $this->conf['pz2-neuerwerbungenCSSPath']);
		$cssTag->addAttribute('media', 'all');
		$this->response->addAdditionalHeaderData( $cssTag->render() );

		// Add pz2-neuerwerbungen.js to <head>.
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->addAttribute('src', $this->conf['pz2-neuerwerbungenJSPath']) ;
		$scriptTag->forceClosingTag(true);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

		// Make jQuery initialise pazpar2neuerwerbungen when the DOM is ready.
		$jsCommand = 'jQuery(document).ready(pz2neuerwerbungenDOMReady);';
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->setContent($jsCommand);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );
	}


}
?>
