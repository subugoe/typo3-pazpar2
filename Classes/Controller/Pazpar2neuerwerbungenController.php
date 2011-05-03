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
		$defaults['neuerwerbungen-subjects'] = '';

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
		$rootGOK = $this->conf['neuerwerbungen-subjects'];
		$subjects = $this->getSubjectsArray($rootGOK);

		// Figure out selected subjects from arguments. Subject argument names
		// are "pz2subject-x0-x1-x2-..." where xi is the index of the subject group
		// at level i.
		$arguments = $this->request->getArguments();
		if ($arguments['button']) {
			$selectedCheckboxes = Array();
			// Our form was submitted: use form values only.
			foreach ($arguments as $argumentName => $argument) {
				$fieldNameStart = 'pz2subject-';
				if (($argument != '') && (strpos($argumentName, $fieldNameStart) === 0)) {
					$nameParts = explode('-', substr($argumentName, strlen($fieldNameStart)));
					if (count($nameParts) > 0) {
						$mySubjects =& $subjects;
						while (count($nameParts) > 1) {
							$subjectIndex = intval(array_shift($nameParts));
							$mySubjects =& $mySubjects[$subjectIndex]['subjects'];
						}

						$subjectIndex = intval(array_shift($nameParts));
						$subject =& $mySubjects[$subjectIndex];
						$subject['selected'] = True;
						$selectedCheckboxes[] = implode(',', $subject['GOKs']);
					}
				}
			}

			// Also write the selected values to our cookie.
			$cookieString = implode(':', $selectedCheckboxes);
			setcookie('pz2neuerwerbungen-previousQuery', $cookieString);
		}
		else {
			// Our form was not submitted: use cookie to set the selected checkboxes.
			$previousQuery = $_COOKIE['pz2neuerwerbungen-previousQuery'];
			$queryItems = explode(':', $previousQuery);

			// Turn on the selection for each group if its GOKs have been passed
			// in the cookie xor for each included subject if its GOKs have been
			// passed in the cookie.
			foreach ($subjects as &$group) {
				$GOKsString = implode(',', $group['GOKs']);
				if (in_array($GOKsString, $queryItems)) {
					$group['selected'] = True;
				}
				else {
					foreach ($group['subjects'] as &$subject) {
						$GOKsString = implode(',', $subject['GOKs']);
						if (in_array($GOKsString, $queryItems)) {
							$subject['selected'] = True;
						}
					}
				}
			}
		}

		// Turn on the selection for the group if all containing subjects are selected
		foreach ($subjects as &$group) {
			$this->turnOnGroupSelectionIfNeeded($group);
		}
		
		// Turn on the selection for all included subjects if the containing group is selected.
		foreach ($subjects as &$group) {
			$this->turnOnChildSelectionIfNeeded($group);
		}

		$this->pz2Neuerwerbungen->setSubjects($subjects);
	}



	/**
	 * Takes the passed $group array and sets its 'selected' field to True if the 'selected' fields
	 * of all the objects in its 'subjects' array are set to True. Uses recursion to check
	 * potentially existant nested groups.
	 *
	 * @param array $group (passed by reference)
	 * @return void
	 */
	private function turnOnGroupSelectionIfNeeded (&$group) {
		$isSelected = True;
		foreach ($group['subjects'] as &$subject) {
			if ($subject['subjects']) {
				$this->turnOnGroupSelectionIfNeeded($subject);
			}
			$isSelected &= $subject['selected'];
		}

		if ($isSelected) {
			$group['selected'] = True;
		}
	}



	/**
	 * Checks whether the 'selected' field of the passed $group array is true and recursively sets
	 * the 'selected' fields of all contained subjects in the 'subject' element to True if that is
	 * the case.
	 *
	 * @param array $group (passed by reference)
	 * @return void
	 */
	private function turnOnChildSelectionIfNeeded (&$group) {
		if ($group['selected'] == True) {
			foreach ($group['subjects'] as &$subject) {
				$subject['selected'] = True;
				if ($subject['subjects']) {
					$this->turnOnChildSelectionIfNeeded($subject);
				}
			}
		}
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
	 * @param string $wildcard appended to each extracted GOK
	 * @return array of GOK strings
	 */
	private function selectedGOKsInFormWithWildcard($wildcard) {
		$subjects = $this->pz2Neuerwerbungen->getSubjects();
		$GOKs = $this->selectedGOKsInGroupWithWildcard($subjects, $wildcard);
		return $GOKs;
	}



	/**
	 * Return the array of all GOKs selected in a subject group, taking into account
	 *  group checkboxes.
	 *
	 * @param array $subjects
	 * @param string $wildcard appended to each extracted GOK
	 * @return array of GOK strings
	 */
	private function selectedGOKsInGroupWithWildcard($subjects, $wildcard) {
		$GOKs = Array();

		foreach ($subjects as $subject) {
			if ($subject['selected'] && $subject['GOKs']) {
				$this->addSearchTermsToList($subject['GOKs'], $GOKs, $wildcard);
			}
			elseif ($subject['subjects']) {
				$GOKs = array_merge($GOKs, $this->selectedGOKsInGroupWithWildcard($subject['subjects'], $wildcard));
			}
		}
		return $GOKs;
	}
	
	
	
	/**
	 * Return an array containing the selected month(s). If no month is selected
	 *  use the previous month.
	 * 
	 * @param string $wildcard
	 * @return array 
	 */
	private function selectedMonthInFormWithWildcard($wildcard) {
		$arguments = $this->request->getArguments();
		$months = explode(',', $arguments['months']);
		// If there is no selection, use the previous month (i.e. the item at
		// index 0 of the monthsArray() result).
		if ($months[0] == '') {
			$monthKeysArray = array_keys($this->monthsArray());
			$months = Array($monthKeysArray[1]);
		}

		$dates = Array();
		$this->addSearchTermsToList($months, $dates, $wildcard);

		return $dates;
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

			$dates = $this->selectedMonthInFormWithWildcard($wildcard);
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
	 * Return array of subjects for the parentPPN passed.
	 * The data needed are loaded from the tx_nkwgok_data table of the database.
	 * They are expected to be imported from CSV-data by the GOK Plug-In. See its documentation
	 * or code for the fields required in teh CSV-file.
	 * The information is converted to nested arrays as required by the 'neuerwerbungen-form'
	 * Partial that handles the display. The data format is:
	 *	* Array [subject groups]
	 *		* Array [subject group, associative]
	 *			* name => string - name of subject group [required]
	 *			* GOKs => Array [optional]
	 *				* string that is a truncated GOK notation
	 *			* subjects => Array [required, subjects]
	 *				* Array [subject, associative]
	 *					* name => string - name of subject group [required]
	 *					* GOKs => Array [required]
	 *						* string that is a truncated GOK notation
	 *					* inline => boolean - displayed in one line with other items?
	 *						[optional, defaults to false]
	 *					* break => boolean - insert <br> before current element?
	 *						[optional, should only be used with inline => true, defaults to false]
	 *
	 * If the GOKs field of a subject group is not specified, create it by taking
	 *	the union of the GOKs arrays of all its subjects.
	 *
	 * @param string $parentPPN
	 * @return Array subjects to be displayed
	 */
	private function getSubjectsArray ($parentPPN) {
		$rootNodes = $this->queryForChildrenOf($parentPPN);
		$subjects = array();

		while($nodeRecord = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rootNodes)) {
			$subject = array();
			$subject['name'] = $nodeRecord['descr']; // TODO: localise

			// Recursively add child elements if they exist.
			if ($nodeRecord['childcount'] > 0) {
				$subject['subjects'] = $this->getSubjectsArray($nodeRecord['ppn']);
			}

			// Extract each search term from the 'search' field and add an array with all of them.
			if ($nodeRecord['search'] != '') {
				$searchComponents = array();
				foreach (explode( ' or ', urldecode($nodeRecord['search'])) as $searchComponent) {
					$component = trim($searchComponent, ' *');
					$component = preg_replace('/^LKL /', '', $component);
					$searchComponents[] = trim($component);
				}
				$subject['GOKs'] = $searchComponents;
			}
			else {
				$subGOKs = array();
				foreach ($subject['subjects'] as $subsubject) {
					foreach ($subsubject['GOKs'] as $subGOK) {
						$subGOKs[] = $subGOK;
					}
				}
				$subject['GOKs'] = $subGOKs;
			}

			// Add tag fields to subject (inline and break).
			foreach (explode(',', $nodeRecord['tags']) as $tag) {
				if ($tag != '') {
					$subject[$tag] = True;
				}
			}

			$subjects[] = $subject;
		}

		return $subjects;
	}



	/**
	 * Queries the database for all records having the $parentGOK parameter as their parent element
	 *  and returns the query result.
	 *
	 * Uses table tx_nkwgok_data from the GOK extension.
	 *
	 * @param string $parentGOK
	 * @return array
	 */
	private function queryForChildrenOf ($parentGOK) {
		$queryResults = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_nkwgok_data',
			"parent = '" . $parentGOK . "'",
			'',
			'gok ASC',
			'');

		return $queryResults;
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
