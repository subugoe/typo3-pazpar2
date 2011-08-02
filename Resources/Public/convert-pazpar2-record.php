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

/*
 * The scripts expects to receive two parameters:
 *	* q - containing a UTF-8 encoded XML serialisation of a pazpar2 location record
 *  * format - containing the name of the format to convert the record to
 * 
 * It returns the result of converting the pazpar2 record to the desired format. 
 */

function transform (&$errorMessage) {
	$formats = Array (
		'ris' => Array(
			'xsl' => 'pz2-to-ris.xsl',
			'content-type' => 'text',
			'filename' => 'export.ris',
			'disposition' => 'inline'
		),
		'endnote' => Array(
			'xsl' => 'pz2-to-ris.xsl',
			'content-type' => 'application/x-research-info-systems',
			'filename' => 'export.enw',
			'disposition' => 'attachment'
		),
		'bibtex' => Array(
			'xsl' => 'pz2-to-bibtex.xsl',
			'content-type' => 'application/x-bibtex',
			'filename' => 'export.bib',
			'disposition' => 'attachment'
		)
	);
	
	$parameters = array_merge($_GET, $_POST);
	
	$errorMessage = Null;
	if (array_key_exists('q', $parameters) && array_key_exists('format', $parameters)) {
		$format = $formats[$parameters['format']];
		if ($format !== Null) {
			$xslPath = '../Private/XSL/' . $format['xsl'];
			$xsl = new DOMDocument();
			$xsl->load($xslPath);
			$xsltproc = new XSLTProcessor();
			$xsltproc->importStylesheet($xsl);

			$xml = new DOMDocument();
			if ($xml->loadXML($parameters['q'])) {
				header('Content-Type: ' . $format['content-type'] . ';charset=utf-8');
				if (array_key_exists('disposition', $format)) {
					$headerString = 'Content-Disposition: ' . $format['disposition'];
					if (array_key_exists('filename', $parameters)) {
						$headerString .= '; filename=' . $parameters['filename'];
					}
					else if (array_key_exists ('filename', $format)) {
						$headerString .= '; filename=' . $format['filename'];
					}
					header($headerString);
				}
				echo($xsltproc->transformToXml($xml));
			}
			else {
				$errorMessage = 'Failed to parse parameter »q« as XML.';
			}
		}
		else {
			$errorMessage = 'Paramter »format« needs to be one of: ' . implode(', ', array_keys($formats));
		}
	}
	else {
		$errorMessage = 'At least one of the required parameters »q« and »format« is missing.';
	}
	
	return ($errorMessage === Null);
}


$errorMessage = Null;

if (!transform($errorMessage)) {
	echo('Error: ' . $errorMessage);
}
?>
