<?php

declare(strict_types=1);

use Ameos\Chatbot\Controller\Backend\ChatbotController;

return [
    'ameos_chatbot_question' => [
        'path' => '/ameos/chatbot/question',
        'target' => ChatbotController::class . '::askAction'
    ]
];
