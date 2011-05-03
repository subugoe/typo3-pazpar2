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
 * Pazpar2neuerwerbungen.php
 *
 * Pazpar2neuerwerbungen model class.
 *
 * @author Sven-S. Porst <porst@sub-uni-goettingen.de>
 */




/**
 * pazpar2 Neuerwerbungen model object.
 */
class Tx_Pazpar2_Domain_Model_Pazpar2neuerwerbungen extends Tx_Extbase_DomainObject_AbstractEntity {


	/**
	 * PPN (i.e. an ID) of the root element used for the Neuerwerbungen subject list.
	 * A record with this value in the 'ppn' field should be in the tx_nkwgok_data table.
	 * @var string
	 */
	protected $rootPPN;

	/**
	 * @return string
	 */
	public function getRootPPN () {
		return $this->rootPPN;
	}

	/**
	 * @param string $newRootGOK
	 * @return void
	 */
	public function setRootPPN ($newRootPPN) {
		$this->rootPPN = $newRootPPN;
	}



	/**
	 * Stores the request’s arguments needed to determine the parameters submitted by the user.
	 * @var array
	 */
	protected $requestArguments;

	/**
	 * @return array
	 */
	public function getRequestArguments () {
		return $this->requestArguments;
	}

	/**
	 * @param array $newRequestArguments
	 * @return void
	 */
	public function setRequestArguments ($newRequestArguments) {
		$this->requestArguments = $newRequestArguments;
	}



	/**
	 * Array with subject tree.
	 * @var Array
	 */
	protected $subjects;
	
	/**
	 * @return array
	 */
	public function getSubjects () {
		if ($this->subjects == Null) {
			$this->setupSubjects();
		}

		return $this->subjects;
	}



	/**
	 * Return array of subjects for the parentPPN passed.
	 * The data needed are loaded from the tx_nkwgok_data table of the database.
	 * They are expected to be imported from CSV-data by the GOK Plug-In. See its documentation
	 * or code for the fields required in teh CSV-file.
	 *
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
	 * @return array subjects to be displayed
	 */
	private function makeSubjectsArrayForPPN ($parentPPN) {
		$rootNodes = $this->queryForChildrenOf($parentPPN);
		$subjects = array();

		while($nodeRecord = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rootNodes)) {
			$subject = array();
			$subject['name'] = $nodeRecord['descr']; // TODO: localise

			// Recursively add child elements if they exist.
			if ($nodeRecord['childcount'] > 0) {
				$subject['subjects'] = $this->makeSubjectsArrayForPPN($nodeRecord['ppn']);
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
	 * Queries the database for all records having the $parentPPN parameter as their parent element
	 *  and returns the query result.
	 *
	 * Uses table tx_nkwgok_data from the GOK extension.
	 *
	 * @param string $parentPPN
	 * @return array
	 */
	private function queryForChildrenOf ($parentPPN) {
		$queryResults = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_nkwgok_data',
			"parent = '" . $parentPPN . "'",
			'',
			'gok ASC',
			'');

		return $queryResults;
	}



	/**
	 * Get the subjects array, add information about the selected ones
	 *  from our arguments to it, and store it.
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
		$subjects = $this->makeSubjectsArrayForPPN($this->rootPPN);

		// Figure out selected subjects from the request’s arguments. Subject argument names
		// are "pz2subject-x0-x1-x2-..." where xi is the index of the subject group
		// at level i.
		if ($this->requestArguments['button']) {
			$selectedCheckboxes = Array();
			// Our form was submitted: use form values only.
			foreach ($this->requestArguments as $argumentName => $argument) {
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

		$this->subjects = $subjects;
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
	 * Array used for month selection menu.
	 * Array elements are associative arrays with two elements:
	 * 	* name - the localised string used for display, e.g. Oktober 2010
	 *	* searchTerms - string used to query the catalogue’s DTM field, e.g. 201010.
	 * 		In case several months are used, the different strings are comma separated.
	 *
	 * @var array
	 */
	protected $months;

	/**
	 * @return Array
	 */
	public function getMonths () {
		if ($this->months == Null) {
			$this->months = $this->monthsArray();
		}
		return $this->months;
	}
	
	/**
	 * @param Array $newMonths
	 * @return void
	 */
	public function setMonths ($newMonths) {
		$this->months = $newMonths;
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
	public function monthsArray ($numberOfMonths = 13) {
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
	 * Value of the selected item in the month selection menu:
	 * The key of the second item in the $months array.
	 *
	 * @return string|null value of default month or null
	 */
	public function getDefaultMonth () {
		$result = null;

		$keys = array_keys($this->getMonths());
		if ( count($keys) >= 2 ) {
			$result = (string) $keys[1];
		}

		return $result;
	}
	
}

?>
