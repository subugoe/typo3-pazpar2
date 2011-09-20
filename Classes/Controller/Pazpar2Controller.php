<?php
/*******************************************************************************
 * Copyright notice
 *
 * Copyright (C) 2010-2011 by Sven-S. Porst, SUB Göttingen
 * <porst@sub.uni-goettingen.de>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 ******************************************************************************/


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
		$this->query->setQueryFromArguments($arguments);

		$this->view->assign('extended', $arguments['extended']);
		$this->view->assign('queryString', $this->query->getQueryString());
		$this->view->assign('querySwitchFulltext', $this->query->getQuerySwitchFulltext());
		$this->view->assign('queryStringTitle', $this->query->getQueryStringTitle());
		$this->view->assign('querySwitchJournalOnly', $this->query->getQuerySwitchJournalOnly());
		$this->view->assign('queryStringPerson', $this->query->getQueryStringPerson());
		$this->view->assign('queryStringDate', $this->query->getQueryStringDate());
		if ($arguments['useJS'] != 'yes') {
			$this->query->setServiceName($this->conf['serviceID']);
			$this->query->setSortOrder($this->determineSortCriteria($arguments));
			$totalResultCount = $this->query->run();
			$this->view->assign('totalResultCount', $totalResultCount);
			$this->view->assign('results', $this->query->getResults());
		}
		$this->view->assign('conf', $this->conf);
	}



	/**
	 * Determine which sort criteria to use and return them as an array whose
	 *	elements are arrays with two elements: 'fieldName' and 'direction'.
	 * @param Array $arguments
	 * @return Array
	 */
	private function determineSortCriteria ($arguments) {
		$sortCriteria = Array();
		
		if (array_key_exists('sort', $arguments)) {
			// Sort order has been set by select on the page.
			$criteria = explode('--', $arguments['sort']);
			foreach ($criteria as $criterion) {
				$parts = explode('-', $criterion);
				if (count($parts) == 2) {
					$sortCriteria[] = Array (
						'fieldName' => $parts[0],
						'direction' => ($parts[1] == 'd') ? 'descending' : 'ascending'
					);
				}
			}
		}
		else {
			// Use default sort order.
			$sortCriteria = $this->conf['sortOrder'];
		}
		
		return $sortCriteria;
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

		// Set up the service name.
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$jsCommand = "\nmy_serviceID = '" . $this->conf['serviceID'] . "';\n";
		$scriptTag->setContent($jsCommand);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

		// Add pz2-client.js to <head>.
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->addAttribute('src', $this->conf['pz2-clientJSPath']) ;
		$scriptTag->forceClosingTag(true);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );


		// Create additional settings that are needed by pz-client.js.
		$jsVariables = array(
			'useGoogleBooks' => (($this->conf['useGoogleBooks']) ? 'true' : 'false'),
			'useZDB' => (($this->conf['useZDB']) ? 'true' : 'false'),
			'ZDBUseClientIP' => ((!$this->conf['ZDBIP']) ? 'true' : 'false'),
			'useHistogramForYearFacets' => (($this->conf['useHistogramForYearFacets'] == '1') ? 'true' : 'false'),
			'clientIPAddress' => json_encode($_SERVER['REMOTE_ADDR']),
			'preferSUBOpac' => (($this->conf['preferSUBOpac']) ? 'true' : 'false'),
			'provideCOinSExport' => (($this->conf['provideCOinSExport']) ? 'true' : 'false'),
			'showExportLinksForEachLocation' => (($this->conf['showExportLinksForEachLocation']) ? 'true' : 'false'),
			'showKVKLink' => (($this->conf['showKVKLink']) ? 'true' : 'false')
		);
		if ($this->conf['exportFormats']) {
			$jsVariables['exportFormats'] = json_encode(array_keys($this->conf['exportFormats']));
		}
		if ($this->conf['siteName']) {
			$jsVariables['siteName'] = json_encode($this->conf['siteName']);
		}
		if ($this->conf['sortOrder']) {
			$jsVariables['displaySort'] = json_encode(array_values(array_filter($this->conf['sortOrder'])));
		}

		$jsCommand = "\n";
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
