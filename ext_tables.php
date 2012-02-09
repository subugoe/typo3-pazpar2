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
