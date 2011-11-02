<?php
/*******************************************************************************
 * Copyright notice
 *
 * Copyright (C) 2011 by Sven-S. Porst
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
 * Configuration array containing rendering options, in particular:
 * * preferSUBOpac (int)
 * * provideCOinSExport (int)
 * * exportFormats (array of strings)
 * 
 * @var array
 */
private $conf;



/**
 * Registers own arguments.
 */
public function initializeArguments() {
	parent::initializeArguments();
	$this->registerArgument('result', 'array', 'The pazpar2 result to render', true);
	$this->registerArgument('conf', 'array', 'Configuration array', true);
}



/**
 * Main function called by Fluid.
 * 
 * @return string
 */
public function render () {
	$result = $this->arguments['result'];
	$this->conf = $this->arguments['conf'];
	
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

	if ($this->conf['provideCOinSExport'] == 1) {
		// Insert COinS information
		$this->appendCOinSSpansToContainer($result, $li);
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
 * to the title as well as class about this fact.
 * The link’s title and class elements should be set before calling this method.
 * @param DOMElement $link
 */
private function turnIntoNewWindowLink ($link) {
	if ($link) {
		$link->setAttribute('target', 'pz2-linkTarget');
		$oldClass = $link->getAttribute('class');
		$newClass = trim($oldClass . ' pz2-newWindowLink');
		$link->setAttribute('class', $newClass);

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
private function appendJournalInfoToContainer ($result, $container) {
	$outputElement = $this->doc->createElement('span');
	$outputElement->setAttribute('class', 'pz2-journal');

	$journalTitle = $this->appendMarkupForFieldToContainer('journal-title', $result, $container, ' ' . Tx_Extbase_Utility_Localization::translate("In", "Pazpar2") . ": ");
	if ($journalTitle) {
		$this->appendMarkupForFieldToContainer('journal-subpart', $result, $journalTitle, ', ');
		$journalTitle->appendChild($this->doc->createTextNode('.'));
	}
}



/**
 * Turns the array $data, containing arrays of strings for its keys into a
 * string suitable for the title attribute of a COinS style element.
 * @param array $data
 * @return string
 */
private function COinSStringForObject ($data) {
	$infoList = Array();
	foreach ($data as $key => $info) {
		if ($info) {
			foreach ($info as $infoItem) {
				$infoList[] = $key . '=' . rawurlencode($infoItem[0]['values'][0]);
			}
		}
	}
	return implode('&', $infoList);
}



/**
 * Appends DOM SPAN elements with COinS information for $result to $container.
 * @param array $result
 * @param DOMElement $container
 */
private function appendCOinSSpansToContainer ($result, $container) {
	foreach ($result['location'] as $locationAll) {
		$location = $locationAll['ch'];
		$coinsData = Array('ctx_ver' => Array(Array('values' => Array('Z39.88-2004'))));
		
		// title
		$title = '';
		if ($location['md-title']) {
			$title .= $location['md-title'][0]['values'][0];
		}
		if ($location['md-multivolume-title']) {
			$title .= ' ' . $location['md-multivolume-title'][0]['values'][0];
		}
		if ($location['md-title-remainder']) {
			$title .= ' ' . $location['md-title-remainder'][0]['values'][0];
		}
		
		// format info
		if ($location['md-medium'][0]['values']['0'] == 'article') {
			$coinsData['rft_val_fmt'] = Array(Array('values' => Array('info:ofi/fmt:kev:mtx:journal')));
			$coinsData['rft.genre'] = Array(Array('values' => Array('article')));
			$coinsData['rft.atitle'] = Array(Array('values' => Array($title)));
			$coinsData['rft.jtitle'] = $location['md-journal-title'];
			if ($location['md-volume-number'] || $location['md-pages-number']) {
				// We have structured volume or pages information: use that instead of journal-subpart.
				$coinsData['rft.volume'] = $location['md-volume-number'];
				$coinsData['rft.issue'] = $location['md-issue-number'];
				if ($location['md-pages-number']) {
					$pageInfo = explode('-', $location['md-pages-number'][0]['values'][0]);
					$coinsData['rft.spage'] = Array(Array('values' => Array($pageInfo[0])));
					if (count($pageInfo) >= 2) {
						$coinsData['rft.epage'] = Array(Array('values' => Array($pageInfo[1])));
					}
				}
			}
			else {
				// We lack structured volume information: use the journal-subpart field.
				$coinsData['rft.volume'] = $location['md-journal-subpart'];
			}
		}
		else {
			$coinsData['rft_val_fmt'] = Array(Array('values' =>Array('info:ofi/fmt:kev:mtx:book')));
			$coinsData['rft.btitle'] = Array(Array('values' =>Array($title)));
			if ($location['md-medium'][0]['values']['0'] == 'book') {
				$coinsData['rft.genre'] = Array(Array('values' => Array('book')));
			}
			else {
				$coinsData['rft.genre'] = Array(Array('values' => Array('document')));
			}
		}
		
		// authors
		$coinsData['rft.au'] = Array();
		if ($location['md-author']) {
			$coinsData['rft.au'] = array_merge($coinsData['rft.au'], $location['md-author']);
		}
		if ($location['md-other-person']) {
			$coinsData['rft.au'] = array_merge($coinsData['rft.au'], $location['md-other-person']);
		}
		
		// further fields
		$coinsData['rft.date'] = $location['md-date'];
		$coinsData['rft.isbn'] = $location['md-isbn'];
		$coinsData['rft.issn'] = $location['md-issn'];
		$coinsData['rft.source'] = $location['md-catalogue-url'];
		$coinsData['rft.pub'] = $location['md-publication-name'];
		$coinsData['rft.place'] = $location['md-publication-place'];
		$coinsData['rft.series'] = $location['md-series-title'];
		$coinsData['rft.description'] = $location['md-description'];
		$URLs = Array();
		if ($location['md-doi']) {
			$URLs[] = Array(Array('values' => Array('info:doi/' . $location['md-doi'][0]['values'][0])));
		}
		if ($location['md-electronic-url']) {
			$URLs = array_merge($URLs, $location['md-electronic-url']);
		}
		$coinsData['rft_id'] = $URLs;
		
		$span = $this->doc->createElement('span');
		$span->setAttribute('class', 'Z3988');
		$coinsString = $this->COinSStringForObject($coinsData);
		$span->setAttribute('title', $coinsString);
		$container->appendChild($span);
	}
}



/**
 *
 * @param array $result
 * @return DOMElement
 */
private function renderDetails ($result) {
	$detailsDiv = $this->doc->createElement('div');
	$detailsDiv->setAttribute('class', 'pz2-details');
	
	$detailsList = $this->doc->createElement('dl');
	$detailsDiv->appendChild($detailsList);
	$clearSpan = $this->doc->createElement('span');
	$detailsDiv->appendChild($clearSpan);
	$clearSpan->setAttribute('class', 'pz2-clear');

	/*	A somewhat sloppy heuristic to create cleaned up author and other-person
		lists to avoid duplicating names listed in title-responsiblity already:
		* Do _not_ separately display authors and other-persons whose apparent
			surname appears in the title-reponsibility field to avoid duplication.
		* Completely omit the author list if no title-responsibility field is present
			as author fields are used in its place then.
	*/
	$allResponsibility = '';
	
	if ($result['md-title-responsibility']) {
		foreach ($result['md-title-responsibility'] as $responsibility) {
			$allResponsibility .= $responsibility['values'][0] . '; ';
		}
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
	if (count($this->conf['exportFormats']) > 0) {
		$this->appendInfoToContainer( $this->exportLinks($result), $detailsDiv);
	}
	
	return $detailsDiv;
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
				$acronymElement = $this->doc->createElement('abbr');
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

		$detailsData = $this->doc->createElement('dd');
		if ($location['md-medium'][0]['values'][0] != 'article') {
			$this->appendInfoToContainer( $this->detailInfoItem('edition', $location), $detailsData);
			$this->appendInfoToContainer( $this->detailInfoItem('publication-name', $location), $detailsData);
			$this->appendInfoToContainer( $this->detailInfoItem('publication-place', $location), $detailsData);
			$this->appendInfoToContainer( $this->detailInfoItem('date', $location), $detailsData);
			$this->appendInfoToContainer( $this->detailInfoItem('physical-extent', $location), $detailsData);
			// $this->cleanISBNs(); not implemented in PHP version
			$this->appendInfoToContainer( $this->detailInfoItem('isbn', $location), $detailsData);
		}
		$this->appendInfoToContainer( $this->electronicURLs($location, $result), $detailsData);
		$this->appendInfoToContainer( $this->catalogueLink($locationAll), $detailsData);

		// Only append location information if additional details exist
		if ($detailsData->hasChildNodes()) {
			$detailsHeading = $this->doc->createElement('dt');
			$locationDetails[] = $detailsHeading;
			$detailsHeading->appendChild($this->doc->createTextNode(Tx_Extbase_Utility_Localization::translate('Ausgabe', 'Pazpar2') . ':'));
			$locationDetails[] = $detailsData;
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
 * Comparison function for sorting URLs. Put URLs with attributes to the front.
 * @param string|Array $a
 * @param string|Array $b
 * @return int
 */
private function URLSort ($a, $b) {
	if ($a['attrs'] && !$b['attrs']) {
		return -1;
	}
	else if (!$a['attrs'] && $b['attrs']) {
		return 1;
	}
	else {debugster($a);
		return $a['attrs']['originalPosition'] - $b['attrs']['originalPosition'];
	}
}



/**
 * Returns a cleaned and sorted list of the URLs in the md-electronic-url fields.
 * @param Array $location
 * @param Array $result the result containing $location
 * @return type Array subarray of $location without duplicates and sorted
 */
private function cleanURLList ($location, $result) {
	$URLs = $location['md-electronic-url'];
	
	if ($URLs) {
		// Figure out which URLs are duplicates and collect indexes of those to remove.
		$indexesToRemove = Array();
		foreach ($URLs as $URLIndex => &$URLInfo) {
			$URLInfo['attrs']['originalPosition'] = $URLIndex;
			$URL = $URLInfo['values'][0];

			// Check for duplicates in the electronic-urls field.
			for ($remainingURLIndex = $URLIndex + 1; $remainingURLIndex < count($URLs); $remainingURLIndex++) {
				$remainingURLInfo = $URLs[$remainingURLIndex];
				$remainingURL = $remainingURLInfo['values'][0];
				if ($URL == $remainingURL) {
					// Two of the URLs are identical.
					// Keep the one with the title if only one of them has one,
					// keep the first one otherwise.
					$URLIndexToRemove = $URLIndex + $remainingURLIndex;
					if (!$URLInfo['attrs'] && $remainingURLInfo['attrs']) {
						$URLIndexToRemove = $URLIndex;
					}
					$indexesToRemove[$URLIndexToRemove] = true;
				}
			}
			// Check for duplicates among the DOIs.
			$DOIs = $result['md-doi'];
			if ($DOIs) {
				foreach ($DOIs as $DOI) {
					if (strpos($DOI['values'][0], $URL) !== False) {
						$indexesToRemove[$URLIndexToRemove] = true;
						break;
					}
				}
			}
		}

		// Remove the duplicate URLs.
		foreach (array_keys($indexesToRemove) as $index) {
			$URLs[$index] = FALSE;
		}
		$URLs = array_filter($URLs);

		// Re-order URLs so those with explicit labels appear at the beginning.
		usort($URLs, Array($this, "URLSort"));
	}
	
	return $URLs;
}


/**
 * Create markup for URLs in current location data.
 * @param array $location
 * @param array $result the result containing $location
 * @return DOMElement
 */
private function electronicURLs ($location, $result) {
	$electronicURLs = $this->cleanURLList($location, $result);
	$URLsContainer = Null;

	if ($electronicURLs && count($electronicURLs) != 0) {
		$URLsContainer = $this->doc->createElement('span');

		foreach ($electronicURLs as $URLInfo) {
			$linkTexts = Array();
			$linkURL = $URLInfo['values'][0];

			if ($URLInfo['attrs']['name']) {
				$linkTexts[] = $URLInfo['attrs']['name'];
				if ($URLInfo['attrs']['note']) {
					$linkTexts[] = $URLInfo['attrs']['note'];
				}
			}
			else if ($URLInfo['attrs']['note']) {
				$linkTexts[] = $URLInfo['attrs']['note'];
			}
			else if ($URLInfo['attrs']['fulltextfile']) {
				$linkTexts[] = 'Document';
			}
			else {
				$linkTexts[] = 'Link';
			}
			
			$localisedLinkTexts = Array();
			foreach ($linkTexts as $linkText) {
				$localisedLinkText = Tx_Extbase_Utility_Localization::translate('link-description-' . $linkText, 'Pazpar2');
				if (!$localisedLinkText) {
					$localisedLinkText = $linkText;
				}
				$localisedLinkTexts[] = $localisedLinkText; 
			}
			$linkText = '[' . implode(', ', $localisedLinkTexts) . ']';
			
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
 * Returns a link for the current record that points to the catalogue page for that item.
 * @param array $locationAll
 * @return DOMElement
 */
private function catalogueLink ($locationAll) {
	$catalogueURL = $locationAll['ch']['md-catalogue-url'][0]['values'][0];
	if ($catalogueURL) {
		/** Replace links to the Göttingen Opac by GVK-links if:
		 *	1) the user does not have a Göttingen IP and
		 *	2) we are not set up to always server SUB Opace links
		 */
		if (strpos('134.76.', $_SERVER["REMOTE_ADDR"]) !== 0
				&& $this->conf['preferSUBOpac'] == 0) {
			$opacBaseURL = 'http://opac.sub.uni-goettingen.de/DB=1';
			$GVKBaseURL = 'http://gso.gbv.de/DB=2.1';
			$catalogueURL = str_replace($opacBaseURL, $GVKBaseURL, $catalogueURL);
		}
	}
	
	$linkElement = Null;
	$targetName = $locationAll['attrs']['name'];
	if ($catalogueURL && $targetName) {
		$linkElement = $this->doc->createElement('a');
		$linkElement->setAttribute('href', $catalogueURL);
		$linkElement->setAttribute('class', 'pz2-detail-catalogueLink');
		$this->turnIntoNewWindowLink($linkElement);
		$linkTitle = Tx_Extbase_Utility_Localization::translate('Im Katalog ansehen', 'Pazpar2');
		$linkElement->setAttribute('title', $linkTitle);
		/* Try to localise catalogue name, fall back to original target name
			if no localisation is available */
		$linkText = Tx_Extbase_Utility_Localization::translate($targetName, 'Pazpar2');
		if ($linkText === Null) {
			$linkText = $targetName;
		}
		$linkElement->appendChild($this->doc->createTextNode($linkText));
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
 * Returns markup for links to each active export format.
 * 
 * @param array $result
 * @return DOMElement
 */
private function exportLinks ($result) {
	$exportLinks = $this->doc->createElement('div');
	$exportLinks->setAttribute('class', 'pz2-extraLinks');
	$exportLinksLabel = $this->doc->createElement('span');
	$exportLinks->appendChild($exportLinksLabel);
	$exportLinksLabel->setAttribute('class', 'pz2-extraLinksLabel');
	$exportLinksLabel->appendChild($this->doc->createTextNode(Tx_Extbase_Utility_Localization::translate('mehr Links', 'Pazpar2')));
	$extraLinkList = $this->doc->createElement('ul');
	$exportLinks->appendChild($extraLinkList);
	$labelFormat = Tx_Extbase_Utility_Localization::translate('download-label-format-simple', 'Pazpar2');
	if (count($result['location']) > 1) {
		$labelFormat = Tx_Extbase_Utility_Localization::translate('download-label-format-all', 'Pazpar2');
	}

	if ($this->conf['showKVKLink'] == 1) {
		$this->appendInfoToContainer($this->KVKItem($result), $extraLinkList);
	}
	$this->appendExportItemsTo($result['location'], $labelFormat, $extraLinkList);
	// Separate submenus for individual locations not implemented in the PHP version.

	return $exportLinks;
}



/**
 * Appends list items with an export form for each exportFormat to the container.
 * 
 * @param array $locations
 * @param string $labelFormat
 * @param DOMElement $container
 */
private function appendExportItemsTo ($locations, $labelFormat, $container) {
	foreach ($this->conf['exportFormats'] as $name => $active) {
		if ($active) {
			$container->appendChild($this->exportItem($locations, $name, $labelFormat));
		}
	}
}



/**
 * Returns a list item containing the form for export data conversion.
 * The parameters are passed to dataConversionForm.
 * 
 * @param arrray $locations
 * @param string $exportFormat
 * @param string $labelFormat
 * @return DOMElement
 */
private function exportItem ($locations, $exportFormat, $labelFormat) {
	$form = $this->dataConversionForm($locations, $exportFormat, $labelFormat);
	$item = Null;
	if ($form) {
		$item = $this->doc->createElement('li');
		$item->appendChild($form);
	}
	
	return $item;
}



/**
 * Returns form Element containing the record information to initiate data conversion.
 * @param array $locations
 * @param string $exportFormat
 * @param string $labelFormat
 * @return DOMElement
 */
private function dataConversionForm ($locations, $exportFormat, $labelFormat) {
	$recordXML = $this->XMLForLocations($locations);
	$XMLString = $recordXML->saveXML();

	$form = Null;
	if ($XMLString) {
		$form = $this->doc->createElement('form');
		$form->setAttribute('method', 'POST');
		$scriptPath = 'typo3conf/ext/pazpar2/Resources/Public/convert-pazpar2-record.php';
		$scriptGetParameters = Array('format' => $exportFormat);
		if ($GLOBALS['TSFE']->lang) {
			$scriptGetParameters['language'] = $GLOBALS['TSFE']->lang;
		}
		if ($this->conf['siteName']) {
			$scriptGetParameters['filename'] = $this->conf['siteName'];
		}
		$form->setAttribute('action', $scriptPath . '?' . http_build_query($scriptGetParameters));
		
		$qInput = $this->doc->createElement('input');
		$qInput->setAttribute('name', 'q');
		$qInput->setAttribute('type', 'hidden');
		$qInput->setAttribute('value', $XMLString);
		$form->appendChild($qInput);
		
		$submitButton = $this->doc->createElement('input');
		$form->appendChild($submitButton);
		$submitButton->setAttribute('type', 'submit');
		$buttonText = Tx_Extbase_Utility_Localization::translate('download-label-' . $exportFormat, 'Pazpar2');
		$submitButton->setAttribute('value', $buttonText);
		if ($labelFormat) {
			$labelText = str_replace('*', $buttonText, $labelFormat);
			$submitButton->setAttribute('title', $labelText);
		}
	}
	
	return $form;
}



/**
 * Returns XML representing the passed locations.
 * @param type $locations
 * @return DOMDocument
 */
private function XMLForLocations ($locations) {
	$XML = new DOMDocument();
	$locationsElement = $XML->createElement('locations');
	$XML->appendChild($locationsElement);

	foreach ($locations as $location) {
		$locationElement = $XML->createElement('location');
		$locationsElement->appendChild($locationElement);

		// copy attributes
		if (array_key_exists('attrs', $location)) {
			foreach ($location['attrs'] as $attributeName => $attributeContent) {
				$locationElement->setAttribute($attributeName, $attributeContent);
			}
		}

		// copy child elements
		if (array_key_exists('ch', $location)) {
			foreach ($location['ch'] as $fieldName => $fields) {
				foreach ($fields as $field) {
					$childElement = $XML->createElement($fieldName);
					$locationElement->appendChild($childElement);
					$childElement->appendChild($XML->createTextNode($field['values'][0]));

					// copy attributes of child elements
					if (array_key_exists('attr', $field)) {
						foreach ($field['attr'] as $attributeName => $attributeContent) {
							$childElement->setAttribute($attributeName, $attributeContent);
						}
					}
				}
			}
		}
	}

	return $XML;
}



/**
 * Returns a list item containing a link to a KVK catalogue search for data.
 * Uses ISBN or title/author data for the search.
 * @param array $result
 * @return DOMElement
 */
private function KVKItem ($result) {
	$KVKItem = Null;

	// Check whether there are ISBNs and use the first one we find.
	// (KVK does not seem to support searches for multiple ISBNs.)
	$ISBN = Null;
	foreach ($result['location'] as $locationAll) {
		if (array_key_exists('md-isbn', $locationAll['ch'])) {
			$ISBN = $locationAll['ch']['md-isbn'][0]['values'][0];
			// Trim parenthetical information from ISBN which may be in
			// Marc Field 020 $a
			$ISBN = preg_replace('/(\s*\(.*\))/', '', $ISBN);
			break;
		}
	}

	$query = '';
	if ($ISBN) {
		// Search for ISBN if we found one.
		$query .= '&SB=' . $ISBN;
	}
	else {
		// If there is no ISBN only proceed when we are dealing with a book
		// and create a search for the title and author.
		$wantKVKLink = False;
		foreach ($result['md-medium'] as $medium) {
			if ($medium['values'][0] === 'book') {
				$wantKVKLink = True;
				break;
			}
		}

		if ($wantKVKLink) {
			if (array_key_exists('md-title', $result)) {
				$query .= '&TI=' . rawurlencode($result['md-title'][0]['values'][0]);
			}
			if (array_key_exists('md-author', $result)) {
				$authors = Array();
				foreach ($result['md-author'] as $author) {
					$authors[] = $author['values'][0];
				}
				$query .= '&AU=' . implode(' ', $authors);
			}
		}
	}

	if ($query !== '') {
		$KVKLink = $this->doc->createElement('a');
		$KVKLinkURL = 'http://kvk.ubka.uni-karlsruhe.de/hylib-bin/kvk/nph-kvk2.cgi?input-charset=utf-8&Timeout=120';
		$KVKLinkURL .= Tx_Extbase_Utility_Localization::translate('&lang=de', 'Pazpar2');
		$KVKLinkURL .= '&kataloge=SWB&kataloge=BVB&kataloge=NRW&kataloge=HEBIS&kataloge=KOBV_SOLR&kataloge=GBV';
		$KVKLink->setAttribute('href', $KVKLinkURL . $query);
		$label = Tx_Extbase_Utility_Localization::translate('KVK', 'Pazpar2');
		$KVKLink->appendChild($this->doc->createTextNode($label));
		$title = Tx_Extbase_Utility_Localization::translate('deutschlandweit im KVK suchen', 'Pazpar2');
		$KVKLink->setAttribute('title', $title);
		$this->turnIntoNewWindowLink($KVKLink);

		$KVKItem = $this->doc->createElement('li');
		$KVKItem->appendChild($KVKLink);
	}

	return $KVKItem;
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
