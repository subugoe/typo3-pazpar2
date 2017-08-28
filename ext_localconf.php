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

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Configure Plug-Ins

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.' . $_EXTKEY,
    'pazpar2',
    [
        'Pazpar2' => 'index',
    ],
    [
        'Pazpar2' => 'index'
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.' . $_EXTKEY,
    'pazpar2serviceproxy',
    [
        'Pazpar2serviceproxy' => 'index',
    ],
    [
        'Pazpar2serviceproxy' => 'index'
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.' . $_EXTKEY,
    'pazpar2proxy',
    [
        'Proxy' => 'proxy',
    ],
    [
        'Proxy' => 'proxy',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.' . $_EXTKEY,
    'pazpar2neuerwerbungen',
    [
        'Pazpar2neuerwerbungen' => 'index',
    ],
    [
        'Pazpar2neuerwerbungen' => 'index'
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['pazpar2_proxy'] = 'EXT:pazpar2/Classes/Ajax/Proxy.php';
