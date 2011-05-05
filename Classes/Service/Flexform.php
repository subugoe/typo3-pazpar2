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

class tx_Pazpar2_Service_Flexform {

	/**
	 * Called from Flexform to provide menu items with Neuerwerbungen subjects.
	 *
	 * @param array $config
	 * @return array
	 */
	public function buildMenu ($config) {
		$rootNodes = $this->queryForChildrenOf('NE');

		$options = array(array('',''));
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($rootNodes)) {
			$optionTitle = $row['descr'];
			$optionValue = $row['ppn'];
			$options[] = array($optionTitle , $optionValue);
		}

		$config['items'] = array_merge($config['items'], $options);
		return $config;
	}

	
	
	/**
	 * Queries the database for all records having the $parentGOK parameter as their parent element
	 *  and returns the query result.
	 *
	 * This requires the GOK plug-in and its database table to work.
	 *
	 * @param string $parentGOK
	 * @return array
	 */
	private function queryForChildrenOf ($parentGOK) {
		$queryResults = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
			'*',
			'tx_nkwgok_data',
			"parent = '" . $parentGOK . "'",
			'',
			'gok ASC',
			'');

		return $queryResults;
	}

}
?>
