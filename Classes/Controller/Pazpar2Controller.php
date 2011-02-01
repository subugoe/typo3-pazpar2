<?php
/*************************************************************************
 *  Copyright notice
 *
 *  © 2010-2011 Sven-S. Porst, SUB Göttingen <porst@sub.uni-goettingen.de>
 *  All rigs reserved
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
 * pazpar2 controller for the pazpar2 package.
 */
class Tx_Pazpar2_Controller_Pazpar2Controller extends Tx_Extbase_MVC_Controller_ActionController {

	public $query;


	/**
	 * Initialiser
	 *
	 * @return void
	 */
	public function initializeAction () {
		$defaults = $this->defaultSettings();

		foreach ( $defaults as $key => $value ) {
			// If a setting is present and non-empty, use it. Otherwise use the default value.
			if( $this->settings[$key] !== null && $this->settings[$key] !== '' ) {
				$this->conf[$key] = $this->settings[$key];
			} else {
				$this->conf[$key] = $value;
			}
		}

		$this->query = t3lib_div::makeInstance('Tx_Pazpar2_Domain_Model_Query');
		$this->query->setServiceName($this->conf['serviceID']);
	}



	/**
	 * defaultSettings: Return array with default settings.
	 *
	 * @return array
	 */
	protected function defaultSettings () {
		$defaults = array(
			'serviceID' => '',
			'CSSPath' => t3lib_extMgm::siteRelPath('pazpar2') . 'Resources/Public/pz2.css',
			'pz2JSPath' => t3lib_extMgm::siteRelPath('pazpar2') . 'Resources/Public/pz2.js',
			'pz2-clientJSPath' => t3lib_extMgm::siteRelPath('pazpar2') . 'Resources/Public/pz2-client.js',
			'useGoogleBooks' => '1',
			'useZDB' => '1',
		);

		return $defaults;
	}



	/**
	 * Index: Insert pazpar2 CSS <link> and JavaScript <script>-tags into
	 * the page’s <head> which are required to make the search work.
	 *
	 * @return void
	 */
	public function indexAction () {
		$this->view->assign('query', $this->query);

		$this->addResourcesToHead();
	}


	/**
	 * @return void
	 */
	public function findAction () {
		$arguments = $this->request->getArguments();
		$myQueryString = $arguments["queryString"];
		$this->query->setQueryString($myQueryString);

		$this->query->run();

		$this->view->assign('results', $this->query->getResults());

		$this->addResourcesToHead();
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

		// Set up pazpar2 service ID.
		$jsCommand = 'my_serviceID = "' . $this->conf['serviceID'] . '";';
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

		// Add settings for pz2-client.js to <head>.
		$jsCommand = 'useGoogleBooks = ' . (($this->conf['useGoogleBooks']) ? 'true' : 'false') . '; ';
		$jsCommand .= 'useZDB = ' . (($this->conf['useZDB']) ? 'true' : 'false') . ';';
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->setContent($jsCommand);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

		// Make jQuery initialise pazpar2 when the DOM is ready.
		$jsCommand = 'jQuery(document).ready(domReady);';
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->setContent($jsCommand);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );
		
		// Add Google Books support if asked to do so.
		if ( $this->conf['useGoogleBooks'] ) {
			// Structurally this might be better in a separate extension?
			$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
			$scriptTag->addAttribute('type', 'text/javascript');
			$scriptTag->addAttribute('src',  'https://www.google.com/jsapi');
			$scriptTag->forceClosingTag(true);
			$this->response->addAdditionalHeaderData( $scriptTag->render() );
			
			$jsCommand = 'google.load("books", "0");';
			$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
			$scriptTag->addAttribute('type', 'text/javascript');
			$scriptTag->setContent($jsCommand);
			$this->response->addAdditionalHeaderData( $scriptTag->render() );
		}

	}

}
?>
