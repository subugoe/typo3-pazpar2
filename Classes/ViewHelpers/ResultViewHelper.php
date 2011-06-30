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


/**
 * ResultViewHelper.php
 *
 * View Helper for a single pazpar2 result as downloaded and parsed by
 * Tx_Pazpar2_Domain_Model_Query.
 *
 * @author Sven-S. Porst <porst@sub-uni-goettingen.de>
 */




/**
 * View Helper class.
 */
class Tx_Pazpar2_ViewHelpers_ResultViewHelper extends Tx_Fluid_Core_ViewHelper_AbstractViewHelper {


/**
 * DOMDocument used by all functions in this class to create DOMElements.
 *
 * @var DOMDocument
 */
private $doc;



/**
 * @param array $results
 * @return string
 */
public function render ($result) {
	$this->doc = DOMImplementation::createDocument();
	$li = $this->doc->createElement('li');
	$this->doc->appendChild($li);
	$li->setAttribute('class', 'pz2-detailsVisible');

	$iconElement = $this->doc->createElement('span');
	$li->appendChild($iconElement);
	$mediaClass = 'other';
	if (count($result['md-medium']) == 1) {
		$mediaClass = $result['md-medium'][0]['values'][0];
	}
	elseif (count($result['md-medium']) > 1) {
		$mediaClass = 'multiple';
	}

	$iconElement->setAttribute('class', 'pz2-mediaIcon ' . $mediaClass);
	$iconElement->setAttribute('title', Tx_Extbase_Utility_Localization::translate('media-type-' . $mediaClass, 'Pazpar2'));

	// basic title/author information
	$this->appendInfoToContainer($this->titleInfo($result), $li);
	$authors = $this->authorInfo($result);
	$this->appendInfoToContainer($authors, $li);

	// year or journal + year information
	if($result['md-medium'][0]['values'][0] == 'article') {
		$this->appendJournalInfoToContainer($result, $li);
	}
	else {
		$spaceBefore = ' ';
		if ($authors) {
			$spaceBefore = ', ';
		}
		$this->appendMarkupForFieldToContainer('date', $result,  $li, $spaceBefore, '.');
	}

	// detailed information about the publication
	$this->appendInfoToContainer($this->renderDetails($result), $li);
	
	return $this->doc->saveHTML();
}



/**
 * Convenince method to append an item to another one, even if undefineds and arrays are involved.
 * @param $info - the DOM element(s) to insert
 * @param DOMElement $container - the DOM element to insert info to
 */
private function appendInfoToContainer ($info, $container) {
	if ($info && $container) {
		if (is_array($info) == False) {
			$container->appendChild($info);
		}
		else {
			foreach ($info as $infoItem) {
				$container->appendChild($infoItem);
			}
		}
	}
}



/**
 * Add a target attribute to open in our target window and add a note
 * to the title about this fact.
 * The link’s title element should be set before calling this function.
 * @param DOMElement $link
 */
private function turnIntoNewWindowLink ($link) {
	if ($link) {
		$link->setAttribute('target', 'pz2-linkTarget');

		$newTitle = Tx_Extbase_Utility_Localization::translate('Erscheint in separatem Fenster.', 'Pazpar2');
		if ($link->hasAttribute('title')) {
			$oldTitle = $link->getAttribute('title');
			$newTitle = $oldTitle . ' (' . $newTitle . ')';
		}
		$link->setAttribute('title', $newTitle);
	}
}



/**
 * Creates span DOM element and content for a field name; Appends it to the given container.
 * @param string $fieldName
 * @param string $result result array to look the fieldName up in
 * @param DOMElement $container
 * @param string $prepend
 * @param string $append
 * @return DOMElement
 */
private function appendMarkupForFieldToContainer ($fieldName, $result, $container, $prepend='', $append='') {
	$span = Null;
	$fieldContent = $result['md-' . $fieldName][0]['values'][0];

	if ($fieldContent && $container) {
		$span = $this->doc->createElement('span');
		$span->setAttribute('class', 'pz2-' . $fieldName);
		$span->appendChild($this->doc->createTextNode($fieldContent));

		if ($prepend != '') {
			$container->appendChild($this->doc->createTextNode($prepend));
		}
		
		$container->appendChild($span);
		
		if ($append != '') {
			$container->appendChild($this->doc->createTextNode($append));
		}
	}

	return $span;
}



/**
 * Returns DOM SPAN element with markup for the current hit's title.
 * @param array $result
 * @return DOMElement
 */
private function titleInfo ($result) {
	$titleCompleteElement = $this->doc->createElement('span');
	$titleCompleteElement->setAttribute('class', 'pz2-title-complete');

	$titleMainElement = $this->doc->createElement('span');
	$titleCompleteElement->appendChild($titleMainElement);
	$titleMainElement->setAttribute('class', 'pz2-title-main');
	$this->appendMarkupForFieldToContainer('title', $result, $titleMainElement);
	$this->appendMarkupForFieldToContainer('multivolume-title', $result, $titleMainElement, ' ');

	$this->appendMarkupForFieldToContainer('title-remainder', $result, $titleCompleteElement, ' ');
	$this->appendMarkupForFieldToContainer('title-number-section', $result, $titleCompleteElement, ' ');

	$titleCompleteElement->appendChild($this->doc->createTextNode('. '));

	return $titleCompleteElement;
}



/**
 * Returns DOM SPAN element with markup for the current hit's author information.
 * The pre-formatted title-responsibility field is preferred and a list of author
 *	names is used as a fallback.
 * @param array $result
 * @return DOMElement
 */
private function authorInfo ($result) {
	$outputElement = Null;

	$outputText = $result['md-title-responsibility'][0]['values'][0];
	if (!$outputText && $result['md-author']) {
		$authors = Array();
		foreach ($result['md-author'] as $author) {
			$authorName = $author['values'][0];
			$authors[] = $authorName;
		}

		$outputText = implode('; ', $authors);
	}

	if ($outputText) {
		$extraFullStop = '';
		if (strlen($outputText) > 1 && $outputText{strlen($outputText) - 1} != '.') {
			$extraFullStop = '.';
		}

		$outputElement = $this->doc->createElement('span');
		$outputElement->setAttribute('class', 'pz2-item-responsibility');
		$outputElement->appendChild($this->doc->createTextNode($outputText . $extraFullStop));
	}

	return $outputElement;
}



/**
 * Appends DOM SPAN element with the current hit's journal information to linkElement.
 * @param DOMElement $result
 * @param DOMElement $container to append the DOM element to
 */
private function appendJournalInfoToContainer($result, $container) {
	$outputElement = $this->doc->createElement('span');
	$outputElement->setAttribute('class', 'pz2-journal');

	$journalTitle = $this->appendMarkupForFieldToContainer('journal-title', $result, $container, ' ' . Tx_Extbase_Utility_Localization::translate("In", "Pazpar2") . ": ");
	if ($journalTitle) {
		$this->appendMarkupForFieldToContainer('journal-subpart', $result, $journalTitle, ', ');
		$journalTitle->appendChild($this->doc->createTextNode('.'));
	}
}



/**
 *
 * @param array $result
 * @return DOMElement
 */
private function renderDetails ($result) {
	$div = $this->doc->createElement('div');
	$div->setAttribute('class', 'pz2-details');
	
	$detailsList = $this->doc->createElement('dl');
	$div->appendChild($detailsList);
	$clearSpan = $this->doc->createElement('span');
	$div->appendChild($clearSpan);
	$clearSpan->setAttribute('class', 'pz2-clear');

	/*	A somewhat sloppy heuristic to create cleaned up author and other-person
		lists to avoid duplicating names listed in title-responsiblity already:
		* Do _not_ separately display authors and other-persons whose apparent
			surname appears in the title-reponsibility field to avoid duplication.
		* Completely omit the author list if no title-responsibility field is present
			as author fields are used in its place then.
	*/
	$allResponsibility = '';
	foreach ($result['md-title-responsibility'] as $responsibility) {
		$allResponsibility .= $responsibility['values'][0] . '; ';
	}
	if ($result['md-title-responsibility']) {
		$authors = $result['md-author'];
		if ($authors) {
			$result['md-author-clean'] = Array();
			foreach ($authors as $author) {
				$nameParts = explode(",", $author['values'][0]);
				$authorSurname = trim($nameParts[0]);
				if (strpos($allResponsibility, $authorSurname) === False) {
					$result['md-author-clean'][] = $author;
				}
			}
		}
	}
	$otherPeople = $result['md-other-person'];
	if ($otherPeople) {
		$result['md-other-person-clean'] = Array();
		foreach ($otherPeople as $otherPerson) {
			$nameParts = explode(",", $otherPerson['values'][0]);
			$personSurname = trim($nameParts[0]);
			if (strpos($allResponsibility, $personSurname) === False) {
				$result['md-other-person-clean'][] = $otherPerson;
			}
		}
	}

	$this->appendInfoToContainer( $this->detailLineAuto('author-clean', $result), $detailsList);
	$this->appendInfoToContainer( $this->detailLineAuto('other-person-clean', $result), $detailsList);
	$this->appendInfoToContainer( $this->detailLineAuto('abstract', $result), $detailsList);
	$this->appendInfoToContainer( $this->detailLineAuto('description', $result), $detailsList);
	$this->appendInfoToContainer( $this->detailLineAuto('series-title', $result), $detailsList);
	$this->appendInfoToContainer( $this->ISSNsDetailLine($result), $detailsList);
	$this->appendInfoToContainer( $this->detailLineAuto('doi', $result), $detailsList);
	$this->appendInfoToContainer( $this->detailLineAuto('creator', $result), $detailsList);

	$this->appendInfoToContainer( $this->locationDetails($result), $detailsList);
	$this->addZDBInfoIntoElement( $detailsList, $result );
	
	return $div;
}



/**
 * @param string $title
 * @param array $result
 * @return Null|array of DT/DD DOMElements
 */
private function detailLineAuto ($title, $result) {
	$line = Null;
	$element = $this->DOMElementForTitle($title, $result);

	if ($element) {
		$line = $this->detailLine($title, $element);
	}

	return $line;
}



/**
 * @param string $title
 * @param array $informationElements (DOM Elements)
 * @return Null|array of DT/DD DOMElements
 */
private function detailLine ($title, $informationElements) {
	$line = Null;

	if ($informationElements && $title) {
		$headingText = Null;

		if (count($informationElements) == 1) {
			$headingText = Tx_Extbase_Utility_Localization::translate('detail-label-' . $title, 'Pazpar2');
		}
		else {
			$labelKey = 'detail-label-' . $title . '-plural';
			$labelLocalisation = Tx_Extbase_Utility_Localization::translate($labelKey, 'Pazpar2');
			if ($labelLocalisation == '') {
				$labelKey = 'detail-label-' . $title;
				$labelLocalisation = Tx_Extbase_Utility_Localization::translate($labelKey, 'Pazpar2');
			}
			$headingText = $labelLocalisation;
		}

		$infoItems = $this->markupInfoItems($informationElements);


		if ($infoItems) {
			$line = Array();

			$rowTitle = $this->doc->createElement('dt');
			$line[] = $rowTitle;
			$labelNode = $this->doc->createTextNode($headingText . ":");
			$acronym = Tx_Extbase_Utility_Localization::translate('detail-label-acronym-' . $title, 'Pazpar2');
			if ($acronym) {
				$acronymElement = $this->doc->createElement('acronym');
				$acronymElement->setAttribute('title', $acronym);
				$acronymElement->appendChild($labelNode);
				$labelNode = $acronymElement;
			}
			$rowTitle->appendChild($labelNode);

			$rowData = $this->doc->createElement('dd');
			$line[] = $rowData;
			$rowData->appendChild($infoItems);
		}
	}

	return $line;
}



/**
 * Returns marked up version of the DOM items passed, putting them into a list if necessary.
 * @param array $elements (DOM Elements)
 * @return array
 */
private function markupInfoItems ($infoItems) {
	$result = Null;

	if (count($infoItems) == 1) {
		$result = $infoItems[0];
	}
	else {
		$result = $this->doc->createElement('ul');
		foreach ($infoItems as $item) {
			$li = $this->doc->createElement('li');
			$result->appendChild($li);
			$li->appendChild($item);
		}
	}

	return $result;
}



/**
 * Returns markup for each location of the item found from the current data.
 * @param array $result
 * @return array of DOM elements
 */
private function locationDetails ($result) {
	$locationDetails = Array();

	foreach ($result['location'] as $locationAll) {
		$location = $locationAll['ch'];
		$localURL = $locationAll['attrs'][0]['id']; // ????
		$localName = $locationAll['attrs'][0]['name']; // ????

		$detailsHeading = $this->doc->createElement('dt');
		$locationDetails[] = $detailsHeading;
		$detailsHeading->appendChild($this->doc->createTextNode(Tx_Extbase_Utility_Localization::translate('Ausgabe', 'Pazpar2') . ':'));

		$detailsData = $this->doc->createElement('dd');
		$locationDetails[] = $detailsData;

		$this->appendInfoToContainer( $this->detailInfoItem('edition', $location), $detailsData);
		$this->appendInfoToContainer( $this->detailInfoItem('publication-name', $location), $detailsData);
		$this->appendInfoToContainer( $this->detailInfoItem('publication-place', $location), $detailsData);
		$this->appendInfoToContainer( $this->detailInfoItem('date', $location), $detailsData);
		$this->appendInfoToContainer( $this->detailInfoItem('physical-extent', $location), $detailsData);
		// $this->cleanISBNs(); not implemented in PHP version
		$this->appendInfoToContainer( $this->detailInfoItem('isbn', $location), $detailsData);
		$this->appendInfoToContainer( $this->electronicURLs($location, $result), $detailsData);
		$this->appendInfoToContainer( $this->catalogueLink($locationAll), $detailsData);

		if (! $detailsData->hasChildNodes()) {
			$locationDetails = Array();
		}
	}
	
	return $locationDetails;
}



/**
 *
 * @param string $fieldName
 * @param array $location
 * @return NULL|DOMElement
 */
private function detailInfoItem ($fieldName, $location) {
	$infoItem = Null;
	$value = $location['md-' . $fieldName];

	if ($value) {
		$label = Null;
		$labelID = 'detail-label-' + $fieldName;
		$localisedLabelString = Tx_Extbase_Utility_Localization::translate($labelID, 'Pazpar2');

		if ($localisedLabelString != '') {
			$label = $localisedLabelString;
		}

		$content = '';
		foreach ($value as $index => $item) {
			if ($index > 0) { $content .= ', '; }
			$content .= $item['values'][0];
		}
		$content = preg_replace('/^[ ]*/', '', $content);
		$content = preg_replace('/[ ;.,]*$/', '', $content);

		$infoItem = $this->detailInfoItemWithLabel($content, $label);
	}

	return $infoItem;
}



/**
 * @param string $fieldContent
 * @param string $labelName
 * @param boolean $dontTerminate
 * @return Null|DOMElement
 */
private function detailInfoItemWithLabel($fieldContent, $labelName, $dontTerminate = False) {
	$infoSpan = Null;
	if ($fieldContent) {
		$infoSpan = $this->doc->createElement('span');
		$infoSpan->setAttribute('class', 'pz2-info');
		if ($labelName) {
			$infoLabel = $this->doc->createElement('span');
			$infoSpan->appendChild($infoLabel);
			$infoLabel->setAttribute('class', 'pz2-label');
			$infoLabel->appendChild($this->doc->createTextNode($labelName));
			$infoSpan->appendChild($this->doc->createTextNode(' '));
		}
		$infoSpan->appendChild($this->doc->createTextNode($fieldContent));

		if (!$dontTerminate) {
			$infoSpan->appendChild($this->doc->createTextNode('; '));
		}
	}

	return $infoSpan;
}



/**
 * Create markup for URLs in current location data.
 * @param array $location
 * @param array $result the result containing $location
 * @return DOMElement
 */
private function electronicURLs ($location, $result) {
	$electronicURLs = $location['md-electronic-url'];
	$DOIs = $result['md-doi'];
	if ($DOIs && $electronicURLs) {
		foreach ($DOIs as $DOI) {
			foreach ($electronicURLs as $URLIndex => $URLInfo) {
				if (strpos($DOI['values'][0], $URLInfo['values'][0]) !== False) {
					array_splice($electronicURLs, $URLIndex, 1);
					break;
				}
			}
		}
	}
	
	$URLsContainer = Null;

	if ($electronicURLs && count($electronicURLs) != 0) {
		$URLsContainer = $this->doc->createElement('span');

		foreach ($electronicURLs as $URLInfo) {
			$linkText = 'Link'; // default link name
			$linkURL = $URLInfo['values'][0];

			if ($URLInfo['attrs']['name']) {
				$linkText = $URLInfo['attrs']['name'];
			}
			else if ($URLInfo['attrs']['note']) {
				$linkText = $URLInfo['attrs']['note'];
			}
			else if ($URLInfo['attrs']['fulltextfile']) {
				$linkText = 'Document';
			}
			
			$localisedLinkText = Tx_Extbase_Utility_Localization::translate('link-description-' . $linkText, 'Pazpar2');
			if (!$localisedLinkText) {
				$localisedLinkText = $linkText;
			}
			$linkText = '[' . $localisedLinkText . ']';
			
			if ($URLsContainer->hasChildNodes()) {
				$URLsContainer->appendChild($this->doc->createTextNode(', '));
			}

			$link = $this->doc->createElement('a');
			$URLsContainer->appendChild($link);
			$link->setAttribute('href', $linkURL);
			$this->turnIntoNewWindowLink($link);
			$link->appendChild($this->doc->createTextNode($linkText));
		}
		$URLsContainer->appendChild($this->doc->createTextNode('; '));
	}

	return $URLsContainer;
}



/**
 * @var array mapping GBV database names to their IDs.
 */
protected $GBVDatabaseIDs = array(
	'wao' => '1.46',
	'natliz' => '1.50',
	'natlizzss' => '1.55',
	'gvk' => '2.1',
	'opac-de-7' => '2.1', /* map Göttingen Opac to GVK */
	'olc' => '2.3',
	'olcssg-his' => '2.35',
	'olcssg-geo' => '2.38',
	'olcssg-ast' => '2.43',
	'olcssg-ang' => '2.75',
	'olcssg-mat' => '2.77',
	'fachopac-ast' => '2.112',
	'fachopac-fin' => '2.113',
	'fachopac-geo' => '2.114',
	'fachopac-mat' => '2.122',
	'zdb-1-amb' => '2.910',
	'zdb-1-wfr' => '5.1',
	'zdb-1-dfl' => '5.2',
	'zdb-1-elw' => '5.3',
	'zdb-1-ecc' => '5.4',
	'zdb-1-eeb' => '5.5',
	'zdb-1-mml' => '5.6',
	'zdb-1-mme' => '5.7',
	'zdb-1-eai' => '5.8',
	'zdb-1-nel' => '5.9',
	'zdb-1-rth' => '5.10',
	'zdb-1-soj' => '5.62',
	'zdb-1-cup' => '5.72',
	'zdb-1-pio' => '5.55'
);



/**
 * Returns a link for the current record that points to the catalogue page for that item.
 * @param array $locationAll
 * @return DOMElement
 */
private function catalogueLink ($locationAll) {
	$targetURL = $locationAll['attrs']['id'];
	$targetName = $locationAll['attrs']['name'];
	$catalogueURL = $locationAll['ch']['md-catalogue-url'][0]['values'][0];

	if (!$catalogueURL) {
		$PPN = preg_replace('/[a-zA-Z]*([0-9X]*)/', '$1', $locationAll['ch']['md-id'][0]['values'][0]);
		$matches = Null;

		if (strpos('z3950.gbv.de:20012/subgoe_opc', $targetURL) !== False) {
			// Old GBV Z39.50 server for SUB Opac.
			if (preg_match('/^134\.76\./', $_SERVER["REMOTE_ADDR"]) > 0) {
				/* Special case: If the database is Göttingen’s Opac and the user seems
									to be in Göttingen, then link to SUB Göttingen Opac. */
				$catalogueURL = 'http://opac.sub.uni-goettingen.de/DB=1/PPN?PPN=' . $PPN;
			}
			else {
				// General case: Link to GVK.
				$catalogueURL = 'http://gso.gbv.de/DB=2.1/PPNSET?PPN=' . $PPN;
			}
		}
		else if (strpos('sru.gbv.de/natliz', $targetURL) !== False) {
			// match Nationallizenzen natliz and natlizzss on new GBV SRU server: no link
		}
		else if (preg_match('/sru.gbv.de\/([a-zA-Z0-9-]*)/', $targetURL, $matches) > 0) {
			// New GBV SRU server
			$databaseName = $matches[1];
			if ($databaseName == 'opac-de-7' && preg_match('/^134\.76\./', $_SERVER["REMOTE_ADDR"]) > 0) {
				/* Special case: If the database is Göttingen’s Opac and the user seems
									to be in Göttingen, then link to SUB Göttingen Opac. */
				$catalogueURL = 'http://opac.sub.uni-goettingen.de/DB=1/PPN?PPN=' . $PPN;
			}
			else {
				// General case: Link to GVK.
				$databaseID = $this->GBVDatabaseIDs[$databaseName];
				$catalogueURL = 'http://gso.gbv.de/' . $databaseID .'/PPNSET?PPN=' . $PPN;
			}
		}
		else if (preg_match('/gso.gbv.de\/sru\/DB=1.5/', $targetURL) > 0) {
			// match Nationallizenzen 1.50 and 1.55 on old GBV SRU server: no link
		}
		else if (strpos('gso.gbv.de/sru/', $targetURL) !== False) {
			// Old GBV SRU server
			$catalogueURL = preg_replace('/(gso.gbv.de\/sru\/)(DB=[\.0-9]*)/', 'http://gso.gbv.de/$2/PPNSET?PPN=' . $PPN, $targetURL);
		}
		else if (strpos('134.76.176.48:2020/jfm', $targetURL) !== False) {
			$catalogueURL = 'http://www.emis.de/cgi-bin/jfmen/MATH/JFM/quick.html?first=1&maxdocs=1&type=html&format=complete&an=' . $PPN;
		}
		else if (strpos('134.76.176.48:2021/arxiv', $targetURL) !== False) {
			if ($locationAll['ch']['md-electronic-url']) {
				$catalogueURL = $locationAll['ch']['md-electronic-url'][0];
			}
		}
		else if (strpos('pio.chadwyck.co.uk:210/pio', $targetURL) !== False) {
			$catalogueURL = 'http://gateway.proquest.com/openurl?url_ver=Z39.88-2004&res_dat=xri:pio:&rft_dat=xri:pio:article:' . $PPN;
		}
	}

	$linkElement = Null;
	if ($catalogueURL && $targetName) {
		$linkElement = $this->doc->createElement('a');
		$linkElement->setAttribute('href', $catalogueURL);
		$this->turnIntoNewWindowLink($linkElement);
		$linkElement->setAttribute('class', 'pz2-detail-catalogueLink');
		$linkTitle = Tx_Extbase_Utility_Localization::translate('Ansehen und Ausleihen bei:', 'Pazpar2') . ' ' . $targetName;
		$linkElement->setAttribute('title', $linkTitle);
		$linkElement->appendChild($this->doc->createTextNode($targetName));
	}

	return $linkElement;
}



/**
 * @param array $result
 * @return array of DOM Elements
 */
private function ISSNsDetailLine ($result) {
	$ISSNTypes = Array('issn' => '', 'pissn' => 'gedruckt', 'eissn' => 'elektronisch');
	$ISSNList = Array();
	foreach ($ISSNTypes as $ISSNTypeIndex => $ISSNType) {
		$fieldName = 'md-' . $ISSNTypeIndex;
		if ($result[$fieldName]) {
			foreach($result[$fieldName][0]['values'] as $ISSNString) {
				$ISSN = substr($ISSNString, 0, 9);
				if (!in_array($ISSN, $ISSNList)) {
					if ($ISSNType != '') {
						$ISSN .= ' (' . Tx_Extbase_Utility_Localization::translate($ISSNType, 'Pazpar2') . ')';
					}
					$ISSNList[] = $ISSN;
				}
			}
		}
	}
	
	$infoElements = Null;
	if (count($ISSNList) > 0) {
		$infoElements = Array( $this->doc->createTextNode(implode(', ', $ISSNList)) );
	}
	
	return $this->detailLine('issn', $infoElements);
}




// Not implemented in the PHP version.
private function addZDBInfoIntoElement ($container, $result) {

}



/**
 * Removes duplicate entries from an array of pazpar2 result values.
 * 
 * @param array $array of pazpar2 result values (each being an array with the element 'values' containing a 1-element array with the actual string
 * @return array $array
 */
private function pz2ValuesUnique ($array) {
	$newArray = Array();
	foreach ($array as $element) {
		$newArray[] = $element['values'][0];
	}
	
	return array_unique($newArray);
}



/**
 * @param string $title name of the data field
 * @param array $result
 * @return array of DOM elements
 */
private function DOMElementForTitle ($title, $result) {
	$elements = Array();
	$theData = Null;

	if ($result['md-' . $title]) {
		$theData = $this->pz2ValuesUnique($result['md-' . $title]);
		$theData = array_reverse($theData);
		foreach ($theData as $value) {
			$rawDatum = $value;
			$wrappedDatum = Null;
			switch ($title) {
				case 'doi':
					$wrappedDatum = $this->linkForDOI($rawDatum);
					break;

				default:
					$wrappedDatum = $this->doc->createTextNode($rawDatum);
					break;
			}
			$elements[] = $wrappedDatum;
		}
	}

	return $elements;
}



/**
 * @param string $DOI
 * @return DOMElement
 */
private function linkForDOI($DOI) {
	$linkElement = $this->doc->createElement('a');
	$linkElement->setAttribute('href', 'http://dx.doi.org/' + $DOI);
	$this->turnIntoNewWindowLink($linkElement);
	$linkElement->appendChild($this->doc->createTextNode($DOI));

	$DOISpan = $this->doc->createElement('span');
	$DOISpan->appendChild($linkElement);

	return $DOISpan;
}

}

?>
