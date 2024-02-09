<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Controller;

use Ameos\Chatbot\Service\ChatbotService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

final class ChatbotController
{
    /**
     * @param ChatbotService $chatbotService
     */
    public function __construct(private readonly ChatbotService $chatbotService)
    {
    }

    /**
     * ask question
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function askAction(ServerRequestInterface $request): ResponseInterface
    {
        $body = json_decode($request->getBody()->getContents(), true);

        return new JsonResponse([
            'message' => $this->chatbotService->request($body['message'] ?? '')
        ]);
    }
}
