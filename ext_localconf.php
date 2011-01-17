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



// Configure plug-in
Tx_Extbase_Utility_Extension::configurePlugin (
	$_EXTKEY,

	// Need to pass 'Pi1' here (despite other comments mentioning a unique ID).
	'Pi1',

	// Array holding the controller-action-combinations that are accessible
	array(
		// The first controller and its first action will be the default
		'Pazpar2' => 'index,find'
	),

	// Array holding non-cachable controller-action-combinations
	array(

	)

);


?>
