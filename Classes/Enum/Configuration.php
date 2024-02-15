<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Enum;

enum Configuration: string
{
    case EndpointOpenAI = 'openai';
    case EndpointMistralAI = 'mistralai';
    case Endpoint = 'endpoint';
    case ApiKey = 'apikey';
    case Model = 'model';
    case BackendActivation = 'be_activation';
    case Extension = 'chatbot';
    case VisitorSystemPrompt = 'chatbot_system_prompt';
}
