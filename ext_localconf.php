<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

// Configure Plug-Ins

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.'.$_EXTKEY,
    'pazpar2',
    [
        'Pazpar2' => 'index',
    ],
    [
        'Pazpar2' => 'index',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.'.$_EXTKEY,
    'pazpar2serviceproxy',
    [
        'Pazpar2serviceproxy' => 'index',
    ],
    [
        'Pazpar2serviceproxy' => 'index',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.'.$_EXTKEY,
    'pazpar2proxy',
    [
        'Proxy' => 'proxy',
    ],
    [
        'Proxy' => 'proxy',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.'.$_EXTKEY,
    'pazpar2neuerwerbungen',
    [
        'Pazpar2neuerwerbungen' => 'index',
    ],
    [
        'Pazpar2neuerwerbungen' => 'index',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['pazpar2_proxy'] = 'EXT:pazpar2/Classes/Ajax/Proxy.php';
