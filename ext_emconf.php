<?php

/*
 * This file is part of the package t3g/querybuilder.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'QueryBuilder',
    'description' => 'Backend extension for query builder in list module.',
    'category' => 'be',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'author' => 'TYPO3 GmbH',
    'author_email' => 'info@typo3.com',
    'version' => '8.7.1-dev',
    'constraints' => [
        'depends' => [
            'typo3' => '8.3.0-8.7.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
