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
 * Pazpar2Controller.php
 *
 * Provides the main controller for pazpar2 plug-in.
 *
 * @author Sven-S. Porst <porst@sub-uni-goettingen.de>
 */


/**
 * pazpar2 controller for the pazpar2 extension.
 */
class Tx_Pazpar2_Controller_Pazpar2Controller extends Tx_Extbase_MVC_Controller_ActionController {

	/**
	 * Query object handling the pazpar2 logic.
	 * @var Tx_Pazpar2_Domain_Model_Query
	 */
	protected $query;


	
	/**
	 * Initialiser
	 *
	 * @return void
	 */
	public function initializeAction () {
		foreach ( $this->settings as $key => $value ) {
			// Transfer settings to conf
			$this->conf[$key] = $value;
			
			if (strpos($key, 'Path') !== False) {
				// Let TYPO3 try to process path settings as a path, so we can
				// use EXT: in the paths.
				$processedPath = $GLOBALS['TSFE']->tmpl->getFileName($value);
				if ($processedPath) {
					$this->conf[$key] = $processedPath;
				}
			}
		}

		$this->query = t3lib_div::makeInstance('Tx_Pazpar2_Domain_Model_Query');
		$this->query->setServiceName($this->conf['serviceID']);
		$this->query->setQueryFromArguments($this->request->getArguments());
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
		$this->addResourcesToHead();
		$arguments = $this->request->getArguments();

		$this->view->assign('extended', $arguments['extended']);
		$this->view->assign('queryString', $this->query->getQueryString());
		$this->view->assign('queryStringTitle', $this->query->getQueryStringTitle());
		$this->view->assign('querySwitchJournalOnly', $this->query->getQuerySwitchJournalOnly());
		$this->view->assign('queryStringPerson', $this->query->getQueryStringPerson());
		$this->view->assign('queryStringDate', $this->query->getQueryStringDate());
		if ($arguments['useJS'] != 'yes') {
			$totalResultCount = $this->query->run();
			$this->view->assign('totalResultCount', $totalResultCount);
			$this->view->assign('results', $this->query->getResults());
		}
		$this->view->assign('conf', $this->conf);
	}



	/**
	 * Helper: Inserts pazpar2 headers into page.
	 *
	 * @return void
	 */
	protected function addResourcesToHead () {
		// Add pazpar2.css to <head>.
		$cssTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('link');
		$cssTag->addAttribute('rel', 'stylesheet');
		$cssTag->addAttribute('type', 'text/css');
		$cssTag->addAttribute('href', $this->conf['CSSPath']);
		$cssTag->addAttribute('media', 'all');
		$this->response->addAdditionalHeaderData( $cssTag->render() );

		// Add pz2.js to <head>.
		// This is Indexdata’s JavaScript that ships with the pazpar2 software.
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->addAttribute('src',  $this->conf['pz2JSPath']);
		$scriptTag->forceClosingTag(true);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

		// Create various settings for pz2.js and pz2-client.js to.
		$jsVariables = array(
			'my_serviceID' => '"' . $this->conf['serviceID'] . '"',
			'useGoogleBooks' => (($this->conf['useGoogleBooks']) ? 'true' : 'false'),
			'useZDB' => (($this->conf['useZDB']) ? 'true' : 'false'),
			'ZDBUseClientIP' => ((!$this->conf['ZDBIP']) ? 'true' : 'false'),
			'useHistogramForYearFacets' => (($this->conf['useHistogramForYearFacets'] == '1') ? 'true' : 'false'),
			'clientIPAddress' => '"' . $_SERVER['REMOTE_ADDR'] . '"',
			'preferSUBOpac' => (($this->conf['preferSUBOpac']) ? 'true' : 'false'),
			'provideCOinSExport' => (($this->conf['provideCOinSExport']) ? 'true' : 'false'),
			'showExportLinksForEachLocation' => (($this->conf['showExportLinksForEachLocation']) ? 'true' : 'false')
		);
		if ($this->conf['exportFormats']) {
			$exportFormats = array();
			foreach ($this->conf['exportFormats'] as $name => $status) {
				if ($status) {
					$exportFormats[] = $name;
				}
			}
			$jsVariables['exportFormats'] = '["' . implode('", "', $exportFormats) . '"]';
		}
		if ($this->conf['siteName']) {
			$jsVariables['siteName'] = "'" . $this->conf['siteName'] . "'";
		}

		$jsCommand = '';
		foreach ($jsVariables as $name => $value) {
			$jsCommand .= $name . ' = ' . $value . ";\n";
		}
		
		// Set up JavaScript function that is called by nkwgok if asked to do so.
		if ($this->conf['triggeredByNKWGOKMenu']) {
			$jsCommand .= 'function nkwgokMenuSelected(option) {
	var searchTerm = option.getAttribute("query");
	if (searchTerm) {
		triggerSearchForForm(undefined, ["(" +  searchTerm + ")"]);
	}
}
';
		}

		// Add the JavaScript setup commands to <head>.
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->setContent($jsCommand);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

		// Add pz2-client.js to <head>.
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->addAttribute('src', $this->conf['pz2-clientJSPath']) ;
		$scriptTag->forceClosingTag(true);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

		// Load flot graphing library if needed.
		if ( $this->conf['useHistogramForYearFacets'] ) {
			$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
			$scriptTag->addAttribute('type', 'text/javascript');
			$scriptTag->addAttribute('src', $this->conf['flotJSPath']) ;
			$scriptTag->forceClosingTag(true);
			$this->response->addAdditionalHeaderData( $scriptTag->render() );

			$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
			$scriptTag->addAttribute('type', 'text/javascript');
			$scriptTag->addAttribute('src', $this->conf['flotSelectionJSPath']) ;
			$scriptTag->forceClosingTag(true);
			$this->response->addAdditionalHeaderData( $scriptTag->render() );
		}

		// Make jQuery initialise pazpar2 when the DOM is ready.
		$jsCommand = "jQuery(document).ready(domReady);\n";

		// Add Google Books support if asked to do so.
		if ( $this->conf['useGoogleBooks'] ) {
			// Structurally this might be better in a separate extension?
			$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
			$scriptTag->addAttribute('type', 'text/javascript');
			$scriptTag->addAttribute('src',  'https://www.google.com/jsapi');
			$scriptTag->forceClosingTag(true);
			$this->response->addAdditionalHeaderData( $scriptTag->render() );

			$jsCommand .= "google.load('books', '0');\n";
		}

		// Add further JavaScript initialisation commands to <head>.
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->setContent($jsCommand);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

	}

}
?>
