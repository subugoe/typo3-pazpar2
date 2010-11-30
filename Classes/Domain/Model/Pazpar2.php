<?php

class Tx_Pazpar2_Domain_Model_Pazpar2 extends Tx_Extbase_DomainObject_AbstractEntity {

	/*
	 * Address of the search page.
	 *
	 * @var string
	 */
	protected $searchURL;

	/*
	 * Returns the address of the search page.
	 *
	 * @return string Address of the search page
	 */
	public function getSearchURL () {
		return $this->searchURL;
	}

	/*
	 * Sets address of the search page.
	 *
	 * @param string $newSearchURL
	 * retrun void
	 */
	public function setSearchURL ($newSearchURL) {
		$this->searchURL = $newSearchURL;
	}




	/*
	 * Name of the pazpar2 service used.
	 *
	 * @var string
	 */
	protected $serviceName;

	/*
	 * Returns the name of the pazpar2 service used.
	 *
	 * @return string pazpar2 service name
	 */
	public function getServiceName () {
		return $this->serviceName;
	}

	/*
	 * Sets the name of the pazpar2 service to use.
	 *
	 * @param string $newServiceName
	 * retrun void
	 */
	public function setServiceName ($newServiceName) {
		$this->serviceName = $newServiceName;
	}




}


?>
