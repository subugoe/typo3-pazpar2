<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'pazpar2',
    'description' => 'Interface for Index Data’s pazpar2 metasearch middleware',
    'category' => 'plugin',
    'version' => '6.0.0',
    'state' => 'stable',
    'author' => 'SUB Göttingen',
    'author_email' => 'typo3-dev@sub.uni-goettingen.de',
    'author_company' => 'Göttingen State and University Library, Germany http://www.sub.uni-goettingen.de',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.0-9.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
    'dependencies' => '',
    'conflicts' => '',
    'suggests' => 't3jquery,nkwgok',
    'priority' => '',
    'loadOrder' => '',
    'shy' => '',
    'module' => '',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
];
