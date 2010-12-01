<?php

class Tx_Pazpar2_Controller_Pazpar2Controller extends Tx_Extbase_MVC_Controller_ActionController {

	protected $serviceName = '';

	protected $stylesheetPath;

	protected $useGoogleBooks = true;


	public function initializeAction () {
		$this->pazpar2 = &t3lib_div::makeInstance('Tx_Pazpar2_Domain_Model_Pazpar2');
	
		if ( $this->settings['serviceName'] ) {
			$this->serviceName = $this->settings['serviceName'];
		}

		if ( $this->settings['stylesheetPath']) {
			$this->stylesheetPath = $this->settings['stylesheetPath'];
		}
		else {
			$this->stylesheetPath = t3lib_extMgm::siteRelPath('pazpar2') . 'Resources/Public/pz2.css';
		}

		if ( $this->settings['useGoogleBooks']) {
			$this->useGoogleBooks = $this->settings['useGoogleBooks'];
		}



	}


	public function indexAction () {
		$this->addResourcesToHead();
	}


	public function addResourcesToHead () {
		// Add pazpar2.css to <head>.
		$cssTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('link');
		$cssTag->addAttribute('rel', 'stylesheet');
		$cssTag->addAttribute('type', 'text/css');
		$cssTag->addAttribute('href', $this->stylesheetPath);
		$cssTag->addAttribute('media', 'all');
		$this->response->addAdditionalHeaderData( $cssTag->render() );	

		// Add pz2.js to <head>.
		// This is Indexdata’s JavaScript that ships with the pazpar2 software.
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->addAttribute('src',  t3lib_extMgm::siteRelPath('pazpar2') . 'Resources/Public/pz2.js');
		$scriptTag->forceClosingTag(true);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

		// Set up pazpar2 service ID.
		$jsCommand = 'my_serviceID = "' . $this->serviceName . '";';
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->setContent($jsCommand);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

		// Add pz2-client.js to <head>.
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->addAttribute('src',  t3lib_extMgm::siteRelPath('pazpar2') . 'Resources/Public/pz2-client.js');
		$scriptTag->forceClosingTag(true);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

		// Make jQuery initialise pazpar2 when the DOM is ready.
		$jsCommand = 'jQuery(document).ready(domReady);';
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->setContent($jsCommand);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

		// Add Google Books support if asked to do so.
		if ($this->useGoogleBooks) {
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

