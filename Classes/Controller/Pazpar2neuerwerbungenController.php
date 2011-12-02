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
		$this->pz2Neuerwerbungen->setMonthCount($this->conf['numberOfMonths']);
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
		$queryString = $this->pz2Neuerwerbungen->searchQueryWithEqualsAndWildcard();
		$this->query->setQueryString($queryString);
		
		parent::indexAction();

		$this->view->assign('pazpar2neuerwerbungen', $this->pz2Neuerwerbungen);
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
