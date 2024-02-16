<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Service;

use Ameos\Chatbot\Enum\Configuration;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\RateLimiter\RateLimit;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\RateLimiter\Storage\CachingFrameworkStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ChatbotService
{
    /**
     * constructor
     * @param RequestFactory $requestFactory
     * @param ConfigurationService $configurationService
     */
    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly ConfigurationService $configurationService
    ) {
    }

    /**
     * ask question
     *
     * @param string $userPrompt
     * @param array $history
     * @param string $systemPrompt
     * @param string $additionalUserPrompt
     * @return array
     */
    public function request(
        string $userPrompt,
        array $history,
        string $systemPrompt,
        ?string $additionalUserPrompt = null
    ): string {
        $answer = '';
        try {
            $messages = array_merge(
                [['role' => 'system', 'content' => $systemPrompt]],
                $history,
                ($additionalUserPrompt ? [['role' => 'user', 'content' => $additionalUserPrompt]] : []),
                [['role' => 'user', 'content' => $userPrompt]]
            );

            $response = $this->requestFactory->request(
                $this->configurationService->getEndpoint(),
                'POST',
                [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->configurationService->getApiKey()
                    ],
                    RequestOptions::JSON => [
                        'model' => $this->configurationService->getModel(),
                        'messages' => $messages
                    ]
                ]
            );

            if ($response->getStatusCode() === 200) {
                $responseData = json_decode($response->getBody()->getContents(), true);
                $answer = $responseData['choices'][0]['message']['content'] ?? '';
            } else {
                $answer = sprintf(
                    LocalizationUtility::translate('apiError', Configuration::Extension->value, null, 'default'),
                    ''
                );
            }
        } catch (\Exception $e) {
            $answer = sprintf(
                LocalizationUtility::translate('apiError', Configuration::Extension->value, null, 'default'),
                $e->getMessage()
            );
        }
        return $answer;
    }

    /**
     * return client limit
     *
     * @param ServerRequestInterface $request
     * @return RateLimit|false
     */
    public function getClientLimit(ServerRequestInterface $request): RateLimit|false
    {
        $configuration = $this->configurationService->getRateLimiterConfiguration();
        if ($configuration['activation']) {
            unset($configuration['activation']);

            $configuration['id'] = 'chatbot';
            $factory = new RateLimiterFactory(
                $configuration,
                GeneralUtility::makeInstance(CachingFrameworkStorage::class)
            );

            $limiter = $factory->create($request->getAttribute('normalizedParams')->getRemoteAddress());
            return $limiter->consume();
        } else {
            return false;
        }
    }
}
