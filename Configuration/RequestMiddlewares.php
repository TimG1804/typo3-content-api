<?php

return [
    'frontend' => [
        'dmf/content-api/routing' => [
            'target' => \DMF\ContentApi\Middleware\ApiRoutingMiddleware::class,
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
            'after' => [
                'typo3/cms-frontend/site',
            ],
        ],
    ],
];
