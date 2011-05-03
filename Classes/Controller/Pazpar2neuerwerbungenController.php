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
		$this->pz2Neuerwerbungen->setRootPPN($this->conf['neuerwerbungen-subjects']);
		$this->pz2Neuerwerbungen->setRequestArguments($this->request->getArguments());
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
		$this->setupQueryString();
		
		parent::indexAction();

		$this->view->assign('pazpar2neuerwerbungen', $this->pz2Neuerwerbungen);
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
			$monthKeysArray = array_keys($this->pz2Neuerwerbungen->monthsArray());
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
