<?php
/*******************************************************************************
 * Copyright notice
 *
 * Copyright (C) 2010-2012 by Sven-S. Porst, SUB Göttingen
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
	 * Search query parts.
	 * 
	 * @var string|Null
	 */
	protected $queryString = Null;
	protected $querySwitchFulltext = Null;
	protected $queryStringTitle = Null;
	protected $querySwitchJournalOnly = Null;
	protected $queryStringPerson = Null;
	protected $queryStringKeyword = Null;
	protected $queryStringDate = Null;

	/**
	 * @return string|Null
	 */
	public function getQueryString () { return $this->queryString; }
	public function getQuerySwitchFulltext () { return $this->querySwitchFulltext; }
	public function getQueryStringTitle () { return $this->queryStringTitle; }
	public function getQuerySwitchJournalOnly () { return $this->querySwitchJournalOnly; }
	public function getQueryStringPerson () { return $this->queryStringPerson; }
	public function getQueryStringKeyword () { return $this->queryStringKeyword; }
	public function getQueryStringDate () { return $this->queryStringDate; }



	/**
	 * Setter for the main query string.
	 * 
	 * @param string $newQueryString 
	 */
	public function setQueryString ($newQueryString) { 
		$this->queryString = $newQueryString;
	}



	/**
	 * Array containing the sort conditions to use. Each of its elements
	 *	is an array with the fields:
	 *  'fieldName' - a string containing the name of the pazpar2 field to sort by
	 *  'direction' - the string 'ascending' or 'descending'
	 *
	 * @var Array
	 */
	protected $sortOrder = Array();

	public function getSortOrder () { return $this->sortOrder; }
	public function setSortOrder ($newSortOrder) { $this->sortOrder = $newSortOrder; }



	/**
	 * Set search query elements from the request’s arguments array.
	 * 
	 * @param array $newArguments 
	 */
	public function setQueryFromArguments ($newArguments) {
		$this->setQueryString(trim($newArguments['queryString']));
		$this->querySwitchFulltext = ($newArguments['querySwitchFulltext'] != '');
		$this->queryStringTitle = trim($newArguments['queryStringTitle']);
		$this->querySwitchJournalOnly = ($newArguments['querySwitchJournalOnly'] != '');
		$this->queryStringPerson = trim($newArguments['queryStringPerson']);
		$this->queryStringKeyword = trim($newArguments['queryStringKeyword']);
		$this->queryStringDate = trim($newArguments['queryStringDate']);
	}



	/**
	 * Service name to run the query on.
	 *
	 * @var string|Null
	 */
	protected $serviceName = Null;

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



	/**
	 * Name of the institution proving database access (as determined by
	 * the pazpar2-access.php proxy service).
	 *
	 * @var string|Null $institutionName
	 */
	protected $institutionName = Null;

	/**
	 * @return string|Null
	 */
	public function getInstitutionName () {
		return $this->institutionName;
	}

	/**
	 * @param string $newInstitutionName
	 */
	private function setInstitutionName ($newInstitutionName) {
		$this->institutionName = $newInstitutionName;
	}



	/**
	 * False by default, True when the Query finished running.
	 *
	 * @var Boolean
	 */
	protected $didRun = False;

	/**
	 * @return Boolean
	 */
	public function getDidRun () {
		return $this->didRun;
	}

	/**
	 * @param Boolean $newDidRun
	 */
	private function setDidRun ($newDidRun) {
		$this->didRun = $newDidRun;
	}



	/**
	 * Indicates whether all targets in the service are active (as determined
	 * by the pazpar2-access.php proxy service).
	 *
	 * @var Boolean|Null
	 */
	protected $allTargetsActive;

	/**
	 * @return Boolean|Null
	 */
	public function getAllTargetsActive () {
		return $this->allTargetsActive;
	}

	/**
	 * @param Boolean $newAllTargetsActive
	 */
	private function setAllTargetsActive ($newAllTargetsActive) {
		$this->allTargetsActive = $newAllTargetsActive;
	}


	
	/**
	 * URL of the pazpar2 service used.
	 * 
	 * @var string|Null
	 */
	protected $pazpar2BaseURL;

	/**
	 * Return URL of pazpar2 service.
	 * If it is not set, return default URL on localhost.
	 *
	 * @return string
	 */
	public function getPazpar2BaseURL () {
		$URL = 'http://' . t3lib_div::getIndpEnv(HTTP_HOST) . '/pazpar2/search.pz2';
		if ($this->pazpar2BaseURL) {
			$URL = $this->pazpar2BaseURL;
		}
		return $URL;
	}

	/**
	 * Setter for pazpar2BaseURL variable.
	 * 
	 * @param string|Null $newPazpar2BaseURL
	 * @return void
	 */
	public function setPazpar2BaseURL ($newPazpar2BaseURL) {
		$this->pazpar2BaseURL = $newPazpar2BaseURL;
	}



	/**
	 * Array holding the search results after they are downloaded.
	 * The array's element can be displayed by the View Helper class
	 * Tx_Pazpar2_ViewHelpers_ResultViewHelper.
	 *
	 * @var array
	 */
	private $results = array();

	/**
	 * @return array
	 */
	public function getResults() {
		return $this->results;
	}



	/**
	 * The total number of results (including the ones that could not be fetched.
	 *
	 * @var integer
	 */
	private $totalResultCount;

	/**
	 * @return integer
	 */
	public function getTotalResultCount () {
		return $this->totalResultCount;
	}

	/**
	 * @param integer $newTotalResultCount
	 */
	private function setTotalResultCount ($newTotalResultCount) {
		$this->totalResultCount = $newTotalResultCount;
	}



	/**
	 * VARIABLES FOR INTERNAL USE
	 */
	
	/**
	 * Stores session ID while pazpar2 is running.
	 * @var string
	 */
	protected $pazpar2SessionID;

	/**
	 * Stores state of query.
	 * @var Boolean
	 */
	protected $queryIsRunning;

	/**
	 * Stores time the current query was started.
	 * @var int
	 */
	protected $queryStartTime;



	/**
	 * Returns the full query string to use in pazpar2.
	 * 
	 * @return string
	 */
	private function fullQueryString () {
		$queryParts = Array();

		// Main search can be default search or full text search.
		if ($this->queryString) {
			if (!$this->querySwitchFulltext) {
				$queryParts[] = $this->queryString;
			}
			else {
				$queryParts[] = 'fulltext=' . $this->queryString;
			}
			
		}
		// Title search can be proper title or journal title depending on the switch.
		if ($this->queryStringTitle) {
			if (!$this->querySwitchJournalOnly) {
				$queryParts[] = 'title=' . $this->queryStringTitle;
			}
			else {
				$queryParts[] = 'journal=' . $this->queryStringTitle;
			}
		}
		// Person search is a phrase search.
		if ($this->queryStringPerson) {	$queryParts[] = 'person="' . $this->queryStringPerson . '"'; }
		if ($this->queryStringKeyword) { $queryParts[] = 'keyword="' . $this->queryStringKeyword . '"'; }
		if ($this->queryStringDate) { $queryParts[] = 'date=' . $this->queryStringDate; }

		$query = implode(' and ', $queryParts);
		$query = str_replace('*', '?', $query);
		return $query;
	}



	/**
	 * Returns URL to initialise pazpar2.
	 * If $serviceName has been set up, that service is used.
	 *
	 * @return sting
	 */
	private function pazpar2InitURL () {
		$URL = $this->getPazpar2BaseURL() . '?command=init';
		if ($this->getServiceName() != Null) {
			$URL .= '&service=' . urlencode($this->getServiceName());
		}

		return $URL;
	}



	/**
	 * Returns URL for starting a search with the current pazpar2 session.
	 * @return string
	 */
	private function pazpar2SearchURL () {
		$URL = $this->getPazpar2BaseURL() . '?command=search';
		$URL .= '&session=' . $this->pazpar2SessionID;
		$URL .= '&query=' . urlencode($this->fullQueryString());

		return $URL;
	}



	/**
	 * Returns URL for a status request of the current pazpar2 session.
	 * @return string
	 */
	private function pazpar2StatURL () {
		$URL = $this->getPazpar2BaseURL() . '?command=stat';
		$URL .= '&session=' . $this->pazpar2SessionID;

		return $URL;
	}



	/**
	 * Returns URL for downloading pazpar2 results.
	 * The parameters can be used to give the the start record
	 * as well as the number of records required.
	 * 
	 * TYPO3 typically starts running into out of memory errors when fetching
	 * around 1000 records in one go with a 128MB memory limit for PHP.
	 *
	 * @param int $start index of first record to retrieve (optional, default: 0)
	 * @param int $num number of records to retrieve (optional, default: 500)
	 * @return string 
	 */
	private function pazpar2ShowURL ($start=0, $num=500) {
		$URL = $this->getPazpar2BaseURL() . '?command=show';
		$URL .= '&session=' . $this->pazpar2SessionID;
		$URL .= '&query=' . urlencode($this->fullQueryString());
		$URL .= '&start=' . $start . '&num=' . $num;
		$URL .= '&sort=' . urlencode($this->sortOrderString());
		$URL .= '&block=1'; // unclear how this is advantagous but the JS client adds it
		return $URL;
	}



	/**
	 * Returns a string encoding the sort order formatted for use by pazpar2.
	 *
	 * @return string
	 */
	private function sortOrderString () {
		$sortOrderComponents = Array();
		foreach ($this->getSortOrder() as $sortCriterion) {
			$sortOrderComponents[] = $sortCriterion['fieldName'] . ':'
									. (($sortCriterion['direction'] === 'descending') ? '0' : '1');
		}
		$sortOrderString = implode(',', $sortOrderComponents);

		return $sortOrderString;
	}



	/**
	 * Initialise the pazpar2 session and store the session ID in $pazpar2SessionID.
	 */
	protected function initialiseSession () {
		$this->queryStartTime = time();
		$initReplyString = t3lib_div::getURL($this->pazpar2InitURL());
		$initReply = t3lib_div::xml2array($initReplyString);

		if ($initReply) {
			$status = $initReply['status'];
			if ($status == 'OK') {
				$sessionID = $initReply['session'];
				if ($sessionID) {
					$this->pazpar2SessionID = $sessionID;
				}
				else {
					t3lib_div::devLog('did not receive pazpar2 session ID', 'pazpar2', 3);
				}

				// Extract access rights information if it is available.
				if (array_key_exists('accessRights', $initReply)) {
					$accessRights = $initReply['accessRights'];
					$this->setInstitutionName($accessRights['institutionName']);
					$this->setAllTargetsActive($accessRights['allTargetsActive'] === '1');
				}
			}
			else {
				t3lib_div::devLog('pazpar2 init status is not "OK" but "' . $status . '"', 'pazpar2', 3);
			}
		}
		else {
			t3lib_div::devLog('could not parse pazpar2 init reply', 'pazpar2', 3);
		}
	}



	/**
	 * Start a pazpar2 Query.
	 * Requires $pazpar2SessionID to be set.
	 */
	protected function startQuery () {
		$this->initialiseSession();

		if ($this->pazpar2SessionID) {
			$searchReplyString = t3lib_div::getURL($this->pazpar2SearchURL());
			$searchReply = t3lib_div::xml2array($searchReplyString);

			if ($searchReply) {
				$status = $searchReply['status'];
				if ($status == 'OK') {
					$this->queryIsRunning = True;
				}
				else {
					t3lib_div::devLog('pazpar2 search command status is not "OK" but "' . $status . '"', 'pazpar2', 3);
				}
			}
			else {
				t3lib_div::devLog('could not parse pazpar2 search reply', 'pazpar2', 3);
			}
		}
	}

	

	/**
	 * Checks whether the query is done.
	 * Requires a session to be established.
	 *
	 * @param int $count return by reference the current number of results
	 * @return boolean True when query has finished, False otherwise
	 */
	protected function queryIsDone () {
		$result = False;

		$statReplyString = t3lib_div::getURL($this->pazpar2StatURL());
		$statReply = t3lib_div::xml2array($statReplyString);

		if ($statReply) {
			// The progress variable is a string representing a number between
			// 0.00 and 1.00. 
			// Casting it to int gives 0 as long as the value is < 1.
			$progress = (int)$statReply['progress'];
			$result = ($progress === 1);
			if ($result === True) {
				// We are done: note that and get the record count.
				$this->setDidRun(True);
				$this->setTotalResultCount($statReply['hits']);
			}
		}
		else {
			t3lib_div::devLog('could not parse pazpar2 stat reply', 'pazpar2', 3);
		}

		return $result;
	}



	/**
	 * Attempts to extract dates from $record and returns an array
	 * containing numbers from the 'date' fields as integers.
	 *
	 * @param Array $record location or full pazpar2 record
	 * @return Array of integers
	 */
	private function extractNewestDates ($record) {
		$result = Array();

		if (array_key_exists('md-date', $record['ch'])) {
			foreach($record['ch']['md-date'] as $date) {
				$dateParts = preg_match_all('/[0-9]{4}/', $date['values'][0], $matches, PREG_SET_ORDER);
				if ($matches && count($matches) > 0 ) {
					$parsedDate = $matches[count($matches)-1][0];
					if (is_numeric($parsedDate)) {
						$result[] = (int)$parsedDate;
					}
				}
			}
		}
		return $result;
	}


	
	/**
	 * Auxiliary sort function for sorting records and locations based
	 * on their 'date' field with the newest item being first and undefined
	 * dates last.
	 *
	 * @param Array $a location or full pazpar2 record
	 * @param Array $b location or full pazpar2 record
	 * @return integer
	 */
	private function yearSort($a, $b) {
		$aDates = $this->extractNewestDates($a);
		$bDates = $this->extractNewestDates($b);

		if (count($aDates) > 0 && count($bDates) > 0) {
			return $bDates[0] - $aDates[0];
		}
		else if (count($aDates) > 0 && count($bDates) === 0) {
			return -1;
		}
		else if (count($aDates) === 0 && count($bDates) > 0) {
			return 1;
		}
		else {
			return 0;
		}
	}



	/**
	 * Fetches results from pazpar2.
	 * Requires an established session.
	 *
	 * Stores the results in $results and the total result count in $totalResultCount.
	 */
	protected function fetchResults () {
		$maxResults = 1000; // limit results
		if (count($this->conf['exportFormats']) > 0) {
			// limit results even more if we are creating export data
			$maxResults = $maxResults / (count($this->conf['exportFormats']) + 1);
		}
		$recordsToFetch = 350;
		$firstRecord = 0;

		// get records in chunks of $recordsToFetch to avoid running out of memory
		// in t3lib_div::xml2tree. We seem to typically need ~100KB per record (?).
		while ($firstRecord < $maxResults) {
			$recordsToFetchNow = min(Array($recordsToFetch, $maxResults - $firstRecord));
			$showReplyString = t3lib_div::getURL($this->pazpar2ShowURL($firstRecord, $recordsToFetchNow));
			$firstRecord += $recordsToFetchNow;

			// need xml2tree here as xml2array fails when dealing with arrays of tags with the same name
			$showReplyTree = t3lib_div::xml2tree($showReplyString);
			$showReply = $showReplyTree['show'][0]['ch'];

			if ($showReply) {
				$status = $showReply['status'][0]['values'][0];
				if ($status == 'OK') {
					$this->queryIsRunning = False;
					$hits = $showReply['hit'];

					if ($hits) {
						foreach ($hits as $hit) {
							$myHit = $hit['ch'];
							$key = $myHit['recid'][0]['values'][0];

							// Make sure the 'medium' field exists by setting it to 'other' if necessary.
							if (!array_key_exists('md-medium', $myHit)) {
								$myHit['md-medium'] = Array(0 => Array('values' => Array(0 => 'other')));
							}

							// If there is no title information but series information, use the
							// first series field for the title.
							if (!(array_key_exists('md-title', $myHit) || array_key_exists('md-multivolume-title', $myHit))
									&& array_key_exists('md-series-title', $myHit)) {
								$myHit['md-multivolume-title'] = Array($myHit['md-series-title'][0]);
							}

							// Sort the location array to have the newest item first
							usort($myHit['location'], Array($this, "yearSort"));

							$this->results[$key] = $myHit;
						}
					}
				}
				else {
					t3lib_div::devLog('pazpar2 show reply status is not "OK" but "' . $status . '"', 'pazpar2', 3);
				}
			}
			else {
				t3lib_div::devLog('could not parse pazpar2 show reply', 'pazpar2', 3);
			}
		}
	}



	/**
	 * Public function to run the pazpar2 query.
	 * If the query string is empty, don’t do anything.
	 * 
	 * The results of the query are available via getResults() after this function returns.
	 */
	public function run () {
		if ($this->fullQueryString() !== '') {
			$this->startQuery();
			// Fetching results can take a while. Increase our time limit.
			$maximumTime = 60;
			set_time_limit($maximumTime + 5);

			while (($this->queryIsRunning) && (time() - $this->queryStartTime < $maximumTime)) {
				sleep(2);
				if ($this->queryIsDone()) {
					break;
				}
			}

			$this->fetchResults();
		}
	}

}

?>
