<?php
/********************************************************************
 *  Copyright notice
 *
 *  © 2010 Sven-S. Porst, SUB Göttingen <porst@sub.uni-goettingen.de>
 *  All rights reserved
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  This copyright notice MUST APPEAR in all copies of the script.
 ********************************************************************/


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

	/**
	 * Initialiser
	 *
	 * @return void
	 */
	public function initializeAction () {
		$defaults = array(
			'serviceID' => '',
			'CSSPath' => t3lib_extMgm::siteRelPath('pazpar2') . 'Resources/Public/pz2.css',
			'pz2JSPath' => t3lib_extMgm::siteRelPath('pazpar2') . 'Resources/Public/pz2.js',
			'pz2-clientJSPath' => t3lib_extMgm::siteRelPath('pazpar2') . 'Resources/Public/pz2-client.js',
			'useGoogleBooks' => '1',
		);

		foreach ( $defaults as $key => $value ) {
			if( $this->settings[$key] ) {
				$this->conf[$key] = $this->settings[$key];
			} else {
				$this->conf[$key] = $value;
			}
		}

	}



	/**
	 * Index: Insert pazpar2 CSS <link> and JavaScript <script>-tags into
	 * the page’s <head> which are required to make the search work.
	 *
	 * @return void
	 */
	public function indexAction () {
		$this->addResourcesToHead();
	}



	/**
	 * Helper: Inserts pazpar2 headers into page.
	 *
	 * @return void
	 */
	private function addResourcesToHead () {
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
			
			$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
			$scriptTag->addAttribute('type', 'text/javascript');
			$scriptTag->setContent( 'google.load("books", "0")' );
			$this->response->addAdditionalHeaderData( $scriptTag->render() );
		}

	}


}
?>
