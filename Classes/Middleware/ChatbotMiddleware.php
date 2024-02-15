<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Middleware;

use Ameos\Chatbot\Enum\Configuration;
use Ameos\Chatbot\Exception\TooManyRequestsHttpException;
use Ameos\Chatbot\Service\ChatbotService;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\RateLimiter\Storage\CachingFrameworkStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ChatbotMiddleware implements MiddlewareInterface
{
    private const URI_CHATBOT = '/chatbot';

    /**
     * construct
     *
     * @param ChatbotService $chatbotService
     */
    public function __construct(private readonly ChatbotService $chatbotService)
    {
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
            /** @var NormalizedParams */
            $normalizedParams = $request->getAttribute('normalizedParams');

            $factory = new RateLimiterFactory(
                [
                    'id' => 'chatbot',
                    'policy' => 'sliding_window',
                    'limit' => 30,
                    'interval' => '15 minutes'
                ],
                GeneralUtility::makeInstance(CachingFrameworkStorage::class)
            );

            $limiter = $factory->create($normalizedParams->getRemoteAddress());
            $limit = $limiter->consume();

            if (false === $limit->isAccepted()) {
                throw new TooManyRequestsHttpException('Too many request');
            }

            $configuration = $request->getAttribute('language')->toArray();

            $body = json_decode($request->getBody()->getContents(), true);
            return new JsonResponse(
                [
                    'question' => $body['message'] ?? '',
                    'answer' => $this->chatbotService->request(
                        $body['message'] ?? '',
                        $body['history'] ?? [],
                        $configuration[Configuration::VisitorSystemPrompt->value] ?? '',
                        $configuration[Configuration::VisitorUserPrompt->value] ?? ''
                    )
                ],
                HttpFoundationResponse::HTTP_OK,
                [
                    'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
                    'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp() - time(),
                    'X-RateLimit-Limit' => $limit->getLimit(),
                ]
            );
        }

        return $handler->handle($request);
    }
}
