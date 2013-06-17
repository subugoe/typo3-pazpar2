<?php
/*******************************************************************************
 * Copyright notice
 *
 * Copyright (C) 2013 by Sven-S. Porst <ssp-web@earthlingsoft.net>
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
 * Pazpar2serviceproxyController.php
 *
 * Main controller for pazpar2 Service Proxy plug-in,
 * of the pazpar2 Extension.
 *
 * @author Sven-S. Porst <ssp-web@earthlingsoft.net>
 */



/**
 * Controller for the pazpar2 Service Proxy package.
 */
class Tx_Pazpar2_Controller_Pazpar2serviceproxyController extends Tx_Pazpar2_Controller_Pazpar2Controller {

	/**
	 * Returns the path of the pazpar2 service on the server or NULL.
	 *
	 * @return String|NULL
	 */
	protected function getPazpar2Path () {
		return $this->conf['serviceProxyPath'];
	}



	/**
	 * @return Tx_Pazpar2_Domain_Model_Query
	 */
	protected function createQuery () {
		$query = t3lib_div::makeInstance('Tx_Pazpar2_Domain_Model_QueryServiceProxy');
		$query->setServiceProxyAuthPath($this->conf['serviceProxyAuthPath']);
		$query->setPazpar2Path($this->getPazpar2Path());
		return $query;
	}

	

	/**
	 * Adds <script> element to <head> containing the configuration for the
	 * pazpar2 Service to use.
	 *
	 * @return void
	 */
	protected function addServiceConfigurationToHead () {
		// Add pz2urlrecipe.js to <head> if URL recipes are to be used.
		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->addAttribute('src',  $this->conf['pz2urlrecipeJSPath']);
		$scriptTag->forceClosingTag(true);
		$this->response->addAdditionalHeaderData( $scriptTag->render() );

		// Add Service Proxy configuration to <head> before pz2.js is included.
		$jsVariables = array(
			'useServiceProxy' => 'true',
			'serviceProxyAuthPath' => json_encode($this->conf['serviceProxyAuthPath']),
			'pazpar2Path' => json_encode($this->conf['serviceProxyPath'])
		);

		$jsCommand = "\n";
		foreach ($jsVariables as $name => $value) {
			$jsCommand .= $name . ' = ' . $value . ";\n";
		}

		$scriptTag = new Tx_Fluid_Core_ViewHelper_TagBuilder('script');
		$scriptTag->addAttribute('type', 'text/javascript');
		$scriptTag->setContent($jsCommand);
		$this->response->addAdditionalHeaderData($scriptTag->render());
	}

}
?>
