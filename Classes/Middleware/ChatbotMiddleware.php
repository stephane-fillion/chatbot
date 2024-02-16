<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Middleware;

use Ameos\Chatbot\Exception\TooManyRequestsHttpException;
use Ameos\Chatbot\Service\ChatbotService;
use Ameos\Chatbot\Service\ConfigurationService;
use Ameos\Chatbot\Service\Prompt\FrontendPromptService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use TYPO3\CMS\Core\Http\JsonResponse;

class ChatbotMiddleware implements MiddlewareInterface
{
    private const URI_CHATBOT = '/chatbot';

    /**
     * construct
     *
     * @param ChatbotService $chatbotService
     * @param ConfigurationService $configurationService
     * @param FrontendPromptService $frontendPromptService
     */
    public function __construct(
        private readonly ChatbotService $chatbotService,
        private readonly ConfigurationService $configurationService,
        private readonly FrontendPromptService $frontendPromptService
    ) {
    }

    /**
     * process middleware
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($request->getUri()->getPath() === self::URI_CHATBOT) {
            // get attributes
            $site = $request->getAttribute('site');
            $siteLanguage = $request->getAttribute('language');

            // http headers
            $headers = [];

            // check limit
            $limit = $this->chatbotService->getClientLimit($request);
            if ($limit) {
                if ($limit->isAccepted() === false) {
                    throw new TooManyRequestsHttpException('Too many request');
                }

                $headers = [
                    'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
                    'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp() - time(),
                    'X-RateLimit-Limit' => $limit->getLimit(),
                ];
            }

            // return chatbot response
            $body = json_decode($request->getBody()->getContents(), true);
            return new JsonResponse(
                [
                    'question' => $body['message'] ?? '',
                    'answer' => $this->chatbotService->request(
                        $body['message'] ?? '',
                        $this->configurationService->historyIsKeeped($siteLanguage) ? ($body['history'] ?? []) : [],
                        $this->frontendPromptService->getSystemPrompt($site, $siteLanguage),
                        $this->frontendPromptService->getUserPrompt($siteLanguage)
                    )
                ],
                HttpFoundationResponse::HTTP_OK,
                $headers
            );
        }

        return $handler->handle($request);
    }
}
