<?php

declare(strict_types=1);

use Ameos\Chatbot\Middleware\ChatbotMiddleware;

return [
    'frontend' => [
        'ameos/chatbot/chatbot' => [
            'target' => ChatbotMiddleware::class,
            'after' => [
                'typo3/cms-frontend/base-redirect-resolver',
            ],
            'before' => [
                'typo3/cms-frontend/page-resolver',
            ],
        ]
    ],
    'backend' => [

    ]
];
