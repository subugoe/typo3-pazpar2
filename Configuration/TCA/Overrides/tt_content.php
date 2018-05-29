<?php

// Register plug-in to be listed in the backend.
// The dispatcher is configured in ext_localconf.php.
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Subugoe.pazpar2',
        'pazpar2',
        'pazpar2'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Subugoe.pazpar2',
        'pazpar2serviceproxy',
        'pazpar2 Service Proxy'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Subugoe.pazpar2',
        'pazpar2neuerwerbungen',
        'pazpar2 Neuerwerbungen'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Subugoe.pazpar2',
        'pazpar2proxy',
        'Pazpar2 Proxy'
);

// Add Flex Forms.
$plugInFlexForms = [
        [
                'plugIn' => 'pazpar2',
                'flexForm' => 'Pazpar2',
        ],
        [
                'plugIn' => 'pazpar2neuerwerbungen',
                'flexForm' => 'Pazpar2',
        ],
];

$extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase('pazpar2'));

foreach ($plugInFlexForms as $plugInFlexFormInfo) {
    $fullPlugInName = $extensionName.'_'.$plugInFlexFormInfo['plugIn'];
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$fullPlugInName] = 'pi_flexform';
    $flexFormPath = 'FILE:EXT:pazpar2/Configuration/FlexForms/'.$plugInFlexFormInfo['flexForm'].'.xml';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($fullPlugInName, $flexFormPath);
}
