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
if (!defined ('TYPO3_MODE')) die ('Access denied.');



// Register plug-in to be listed in the backend.
// The dispatcher is configured in ext_localconf.php.
Tx_Extbase_Utility_Extension::registerPlugin (
	$_EXTKEY,
	'pazpar2', // Name used internally by Typo3
	'pazpar2' // Name shown in the backend dropdown field.
);

Tx_Extbase_Utility_Extension::registerPlugin (
	$_EXTKEY,
	'pazpar2neuerwerbungen', // Name used internally by Typo3
	'pazpar2 Neuerwerbungen' // Name shown in the backend dropdown field.
);


// Add flexform for both plug-ins.
$plugInFlexForms = Array (
	Array( 'plugIn' => 'pazpar2', 'flexForm' => 'Pazpar2'),
	Array( 'plugIn' => 'pazpar2neuerwerbungen', 'flexForm' => 'Pazpar2'),
);

$extensionName = strtolower(t3lib_div::underscoredToUpperCamelCase($_EXTKEY));

foreach ($plugInFlexForms as $plugInFlexFormInfo) {
	$fullPlugInName = $extensionName . '_'. $plugInFlexFormInfo['plugIn'];
	$TCA['tt_content']['types']['list']['subtypes_addlist'][$fullPlugInName] = 'pi_flexform';
	$flexFormPath = 'FILE:EXT:' . $_EXTKEY .
					'/Configuration/FlexForms/' . $plugInFlexFormInfo['flexForm'] . '.xml';
	t3lib_extMgm::addPiFlexFormValue($fullPlugInName, $flexFormPath);
}

include_once(t3lib_extMgm::extPath($_EXTKEY) . 'Classes/Service/Flexform.php');

t3lib_extMgm::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'pazpar2 Settings');

?>
