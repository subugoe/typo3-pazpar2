<?php

// Configure Plug-Ins

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.pazpar2',
    'pazpar2',
    [
        'Pazpar2' => 'index',
    ],
    [
        'Pazpar2' => 'index',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.pazpar2',
    'pazpar2serviceproxy',
    [
        'Pazpar2serviceproxy' => 'index',
    ],
    [
        'Pazpar2serviceproxy' => 'index',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.pazpar2',
    'pazpar2proxy',
    [
        'Proxy' => 'proxy',
    ],
    [
        'Proxy' => 'proxy',
    ]
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Subugoe.pazpar2',
    'pazpar2neuerwerbungen',
    [
        'Pazpar2neuerwerbungen' => 'index',
    ],
    [
        'Pazpar2neuerwerbungen' => 'index',
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['pazpar2_proxy'] = \Subugoe\Pazpar2\Ajax\Proxy::class . '::proxyAction';
