<?php
/********************************************************************
 *  Copyright notice
 *
 *  © 2010 Sven-S. Porst, SUB Göttingen <porst@sub.uni-goettingen.de>
 *  All rights reserved
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  This copyright notice MUST APPEAR in all copies of the script.
 ********************************************************************/
if (!defined ('TYPO3_MODE')) die ('Access denied.');



// Register plug-in to be listed in the backend. The dispatcher is configured in ext_localconf.php.
Tx_Extbase_Utility_Extension::registerPlugin(
	$_EXTKEY,
	// Need to pass 'Pi1' here (despite other comments mentioning a unique ID).
	'Pi1',
	// Name shown in the backend dropdown field.
	'pazpar2'
);



// Set up FlexForms.
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';

$flexFormLocation = 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_list.xml';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY . '_pi1', $flexFormLocation);
?>
