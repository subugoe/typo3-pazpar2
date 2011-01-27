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
		$URL = 'http://vlib.sub.uni-goettingen.de/pazpar2/search.pz2';
		// $URL = 'http://localhost/pazpar2/search.pz2';

		return $URL;
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
		$URL .= '&block=1'; // unclear how this is advantagous but the JS client adds it

		return $URL;
	}


	protected function initialiseSession () {
		$this->queryStartTime = time();
		$initReplyString = file_get_contents($this->pazpar2InitURL());
		$initReply = t3lib_div::xml2array($initReplyString);

		if ($initReply) {
			$status = $initReply['status'];
			if ($status == 'OK') {
				$sessionID = $initReply['session'];
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
			debugster('could not parse pazpar2 init reply');
		}
	}


	/*
	 */
	protected function startQuery () {
		$this->initialiseSession();

		if ($this->pazpar2SessionID) {
			debugster($this->pazpar2SearchURL());
			$searchReplyString = file_get_contents($this->pazpar2SearchURL());
			$searchReply = t3lib_div::xml2array($searchReplyString);

			if ($searchReply) {
				$status = $searchReply['status'];
				if ($status == 'OK') {
					$this->queryIsRunning = True;
				}
				else {
					debugster('pazpar2 search command status is not "OK" but ' . $status);
				}
			}
			else {
				debugster('could not parse pazpar2 search reply');
			}

		}
	}

	
	/*
	 */
	protected function queryIsDoneWithResultCount (&$count) {
		$result = False;

		$statReplyString = file_get_contents($this->pazpar2StatURL());
		$statReply = t3lib_div::xml2array($statReplyString);

		if ($statReply) {
			$progress = $statReply['progress'];
			if ($progress == 1) {
				$count = (int)$statReply['records'];
				$result = True;
			}
		}
		else {
			debugster('could not parse pazpar2 stat reply');
		}

		return $result;
	}


	/*
	 *
	 */
	protected function fetchResults () {
		$result = Null;

		$showReplyString = file_get_contents($this->pazpar2ShowURL());
		// need xml2tree here as xml2array fails when dealing with arrays of tags with the same name
		$showReplyTree = t3lib_div::xml2tree($showReplyString);
		$showReply = $showReplyTree['show'][0]['ch'];

		if ($showReply) {
			$status = $showReply['status'][0]['values'][0];
			if ($status == 'OK') {
				$this->queryIsRunning = False;
				$hits = $showReply['hit'];

				foreach ($hits as $hit) {
					$myHit = $hit['ch'];
					$key = $myHit['recid'][0]['values'][0];
					$this->results[$key] = $myHit;
				}
			}
			else {
				debugster('pazpar2 show reply status is not "OK" but ' . $status);
			}
		}
		else {
			debugster('could not parse pazpar2 search reply');
		}
	}



	public function run () {
		$this->startQuery();
		$resultCount = Null;
		$maximumTime = 120;

		set_time_limit($maximumTime + 5);
		
		while (($this->queryIsRunning) && (time() - $this->queryStartTime < $maximumTime)) {
			sleep(2);

			if ($this->queryIsDoneWithResultCount($resultCount)) {
				break;
			}
		}

		$this->fetchResults();
	}



}

?>
