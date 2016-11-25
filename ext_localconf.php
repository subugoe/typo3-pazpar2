<?php
/*******************************************************************************
 * Copyright notice
 *
 * Copyright (C) 2010-2013 by Sven-S. Porst, SUB Göttingen
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


if (!defined('TYPO3_MODE')) die ('Access denied.');


// Configure Plug-Ins

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'Subugoe.' . $_EXTKEY,
		'pazpar2', // Name used internally by TYPO3
		// Array holding the controller-action-combinations that are accessible
		array(
			// The first controller and its first action will be the default
				'Pazpar2' => 'index'
		),
		// Array holding non-cachable controller-action-combinations
		array(
				'Pazpar2' => 'index'
		)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'Subugoe.' . $_EXTKEY,
		'pazpar2serviceproxy', // Name used internally by TYPO3
		// Array holding the controller-action-combinations that are accessible
		array(
			// The first controller and its first action will be the default
				'Pazpar2serviceproxy' => 'index'
		),
		// Array holding non-cachable controller-action-combinations
		array(
				'Pazpar2serviceproxy' => 'index'
		)
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
		'Subugoe.' . $_EXTKEY,
		'pazpar2neuerwerbungen', // Name used internally by TYPO3
		// Array holding the controller-action-combinations that are accessible
		array(
			// The first controller and its first action will be the default
				'Pazpar2neuerwerbungen' => 'index'
		),
		// Array holding non-cachable controller-action-combinations
		array(
				'Pazpar2neuerwerbungen' => 'index'
		)
);
