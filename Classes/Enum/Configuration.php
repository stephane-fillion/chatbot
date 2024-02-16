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
    case VisitorSystemPrompt = 'system_prompt';
    case VisitorUserPrompt = 'user_prompt';
    case DataPrompt = 'data_prompt';
    case KeepHistory = 'keep_history';
}
