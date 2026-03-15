<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Content API',
    'description' => 'API-first headless content API for TYPO3 — stable contracts, DTO-based responses, no TypoScript rendering',
    'category' => 'fe',
    'author' => '3m5. GmbH',
    'author_email' => 'tim.gossrau@3m5.de',
    'author_company' => '3m5. GmbH',
    'state' => 'experimental',
    'version' => '0.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-13.4.99',
            'php' => '8.2.0-8.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
