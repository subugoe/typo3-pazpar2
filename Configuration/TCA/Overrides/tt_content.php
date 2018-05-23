<?php
// Register plug-in to be listed in the backend.
// The dispatcher is configured in ext_localconf.php.
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Subugoe.pazpar2',
        'pazpar2', // Name used internally by TYPO3.
        'pazpar2' // Name shown in the backend dropdown field.
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Subugoe.pazpar2',
        'pazpar2serviceproxy', // Name used internally by TYPO3.
        'pazpar2 Service Proxy' // Name shown in the backend dropdown field.
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Subugoe.pazpar2',
        'pazpar2neuerwerbungen', // Name used internally by TYPO3.
        'pazpar2 Neuerwerbungen' // Name shown in the backend dropdown field.
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
        'Subugoe.pazpar2',
        'pazpar2proxy', // Name used internally by TYPO3.
        'Pazpar2 Proxy' // Name shown in the backend dropdown field.
);

// Add Flex Forms.
$plugInFlexForms = [
        [
                'plugIn' => 'pazpar2',
                'flexForm' => 'Pazpar2'
        ],
        [
                'plugIn' => 'pazpar2neuerwerbungen',
                'flexForm' => 'Pazpar2'
        ],
];

$extensionName = strtolower(\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase('pazpar2'));

foreach ($plugInFlexForms as $plugInFlexFormInfo) {
    $fullPlugInName = $extensionName . '_' . $plugInFlexFormInfo['plugIn'];
    $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$fullPlugInName] = 'pi_flexform';
    $flexFormPath = 'FILE:EXT:pazpar2/Configuration/FlexForms/' . $plugInFlexFormInfo['flexForm'] . '.xml';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($fullPlugInName, $flexFormPath);
}
