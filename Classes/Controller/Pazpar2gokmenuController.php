<?php
/*************************************************************************
 *  Copyright notice
 *
 *  © 2011 Sven-S. Porst, SUB Göttingen <porst@sub.uni-goettingen.de>
 *  All rights reserved
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  This copyright notice MUST APPEAR in all copies of the script.
 *************************************************************************/


/**
 * Pazpar2Controller.php
 *
 * Provides the main controller for pazpar2 plug-in.
 *
 * @author Sven-S. Porst <porst@sub-uni-goettingen.de>
 */


/**
 * pazpar2gokmenu controller for the pazpar2gokmenu extension.
 */
class Tx_Pazpar2_Controller_Pazpar2gokmenuController extends Tx_Pazpar2_Controller_Pazpar2Controller {

	/**
	 * Initialiser
	 *
	 * @return void
	 */
	public function initializeAction () {
		parent::initializeAction();
	}



	/**
	 * defaultSettings: Return array with default settings.
	 *
	 * @return array
	 */
	protected function defaultSettings () {
		$defaults = parent::defaultSettings();

		return $defaults;
	}



	/**
	 * Index:
	 * 1. Insert pazpar2 CSS <link> and JavaScript <script>-tags into
	 * the page’s <head> which are required to make the search work.
	 * 2. Get parameters and run the query. Display results if there are any.
	 *
	 * @return void
	 */
	public function indexAction () {
		parent::indexAction();
	}



	/**
	 * Helper: Inserts pazpar2 headers into page.
	 *
	 * @return void
	 */
	protected function addResourcesToHead () {
		parent::addResourcesToHead();

		// Set up JavaScript function that is called by nkwgok.
		$jsCommand = 'function nkwgokMenuSelected(option) {
	var searchTerm = option.getAttribute("query");
	if (searchTerm) {
		my_paz.search(searchTerm, fetchRecords, null, null);
		resetPage();
		curSearchTerm = searchTerm;
	}
}';
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->setContent($jsCommand);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );
	}

}
?>
