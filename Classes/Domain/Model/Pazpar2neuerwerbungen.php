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
 * Pazpar2neuerwerbungen.php
 *
 * Pazpar2neuerwerbungen model class.
 *
 * @author Sven-S. Porst <porst@sub-uni-goettingen.de>
 */




/**
 * pazpar2 Neuerwerbungen model object.
 */
class Tx_Pazpar2_Domain_Model_Pazpar2neuerwerbungen extends Tx_Extbase_DomainObject_AbstractEntity {


	/**
	 * Array with subject tree.
	 *
	 * @var Array
	 */
	protected $subjects;
	
	/**
	 * Getter for subject tree array.
	 *
	 * @return Array
	 */
	public function getSubjects () {
		return $this->subjects;
	}
	
	/**
	 * Setter for subject tree array.
	 *
	 * @param Array $newSubjects
	 * @return void
	 */
	public function setSubjects ($newSubjects) {
		$this->subjects = $newSubjects;
	}



	/**
	 * Array used for month selection menu.
	 * Array elements are associative arrays with two elements:
	 * 	* name - the localised string used for display, e.g. Oktober 2010
	 *	* searchTerms - string used to query the catalogue’s DTM field, e.g. 201010.
	 * 		In case several months are used, the different strings are comma separated.
	 *
	 * @var Array
	 */
	protected $months;

	/**
	 * Getter for months array.
	 *
	 * @return Array
	 */
	public function getMonths () {
		return $this->months;
	}
	
	/**
	 * Setter for months array.
	 *
	 * @param Array $newMonths
	 * @return void
	 */
	public function setMonths ($newMonths) {
		$this->months = $newMonths;
	}



	/**
	 * Value of the selected item in the month selection menu:
	 * The key of the second item in the $months array.
	 *
	 * @return string|null value of default month or null
	 */
	public function getDefaultMonth () {
		$result = null;

		$keys = array_keys($this->getMonths());
		if ( count($keys) >= 2 ) {
			$result = (string) $keys[1];
		}

		return $result;
	}
	
}

?>
