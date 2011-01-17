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
 * Query.php
 *
 * Query model class.
 *
 * @author Sven-S. Porst <porst@sub-uni-goettingen.de>
 */


/**
 * Query model object.
 */
class Tx_Pazpar2_Domain_Model_Query extends Tx_Extbase_DomainObject_AbstractEntity {


	/**
	 * Search string.
	 * TODO: Escape string?
	 *
	 * @var string
	 */
	protected $queryString;

	/**
	 * @return string
	 */
	public function getQueryString () {
		return $this->queryString;
	}

	/**
	 * @param string $newQueryString
	 * @return void
	 */
	public function setQueryString ($newQueryString) {
		$this->queryString = $newQueryString;
	}



	/**
	 * Service name to run the query on.
	 * TODO: Escape the string?
	 *
	 * @var string|Null
	 */
	protected $serviceName;

	/**
	 * @return string
	 */
	public function getServiceName () {
		return $this->serviceName;
	}

	/**
	 * @param string $newServiceName
	 * @return void
	 */
	public function setServiceName ($newServiceName) {
		$this->serviceName = $newServiceName;
	}


	private $results = array();

	public function getResults() {
		return $this->results;
	}




	protected $pazpar2SessionID;
	protected $queryIsRunning;
	protected $queryStartTime;


	/*
	 * TODO: Make URL configurable (both here and in JS)
	 * @return string
	 */
	private function pazpar2BaseURL () {
		return 'http://localhost/pazpar2/search.pz2';
	}

	private function pazpar2InitURL () {
		$URL = $this->pazpar2BaseURL() . '?command=init';
		if ($this->getServiceName() != Null) {
			$URL .= '&service=' . $this->getServiceName();
		}

		return $URL;
	}


	private function pazpar2SearchURL () {
		$URL = $this->pazpar2BaseURL() . '?command=search';
		$URL .= '&session=' . $this->pazpar2SessionID;
		$URL .= '&query=' . $this->getQueryString();

		return $URL;
	}


	private function pazpar2StatURL () {
		$URL = $this->pazpar2BaseURL() . '?command=stat';
		$URL .= '&session=' . $this->pazpar2SessionID;

		return $URL;
	}



	private function pazpar2ShowURL () {
		$URL = $this->pazpar2BaseURL() . '?command=show';
		$URL .= '&session=' . $this->pazpar2SessionID;
		$URL .= '&query=' . $this->getQueryString();
		$URL .= '&start=0&num=1000';
		$URL .= '&sort=date%3A0%2Cauthor%3A1%2Ctitle%3A1';
		$URL .= '&block=1'; // unclear whether this is advantagous but the JS client adds it

		return $URL;
	}


	protected function initialiseSession () {
		debugster('initialiseSession');
		$this->queryStartTime = time();

		$initReplyString = file_get_contents($this->pazpar2InitURL());
		$initReply = simplexml_load_string($initReplyString);

		if ($initReply) {
			$status = $initReply->xpath('/init/status');
			if ($status == 'OK') {
				$sessionID = $initReply->xpath('/init/session');
				if ($sessionID) {
					$this->pazpar2SessionID = $sessionID;
				}
				else {
					debugster('did not receive pazpar2 session ID');
				}
			}
			else {
				debugster('pazpar2 init status is not "OK" but ' . $status);
			}
		}
		else {
			debugster('could not read pazpar2 init reply');
		}
	}


	/*
	 */
	protected function startQuery () {
		$this->initialiseSession();

		if ($this->pazpar2SessionID) {
			$searchReplyString = file_get_contents($this->pazpar2SearchURL());
			$searchReply = simplexml_load_string($searchReplyString);

			if ($searchReply) {
				$status = $searchReply->xpath('/search/status');
				if ($status == 'OK') {
					$this->queryIsRunning = True;
				}
			}
			else {
				debugster('could not read pazpar2 search reply');
			}

		}
	}

	
	/*
	 */
	protected function queryIsDoneWithResultCount (&$count) {
		$result = False;

		$statReplyString = file_get_contents($this->pazpar2StatURL());
		$statReply = simplexml_load_string($statReplyString);

		if ($statReply) {
			$activeCount = $statReply->xpath('/stat/activeclients');
			if ($activeCount == 0) {
				$count = $statReply->xpath('/stat/records');
				$result = True;
			}
		}
		else {
			debugster('could not parse pazpar2 stat reply');
		}

		return $result;
	}


	protected function fetchResults () {
		$result = Null;

		$showReplyString = file_get_contents($this->pazpar2ShowURL());
		$showReply = simplexml_load_string($showReplyString);

		if ($showReply) {
			$status = $showReply->xpath('/show/status');
			if ($status == 'OK') {
				$this->queryIsRunning = False;

				$hits = $showReply->xpath('/show/hit');
				foreach ($hits as $hit) {
					$key = $hit['recid'];
					$this->results[$key] = $hit;
				}
			}
			else {
				debugster('pazpar2 show reply with non-"OK" status');
			}
		}
		else {
			debugster('could not read pazpar2 search reply');
		}
	}



	public function run () {
		$this->startQuery();

		while (($this->queryIsRunning) && (time() - $this->queryStartTime < 60)) {
			sleep(2);

			$resultCount = Null;
			if ($this->queryIsDoneWithResultCount($resultCount)) {
				$this->fetchResults();
			}
		}
	}



}

?>
