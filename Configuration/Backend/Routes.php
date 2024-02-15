<?php

declare(strict_types=1);

use Ameos\Chatbot\Controller\ContributorController;

return [
    'ameos_chatbot_question' => [
        'path' => '/ameos/chatbot/question',
        'target' => ContributorController::class . '::askAction'
    ]
];
