<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Service;

use Ameos\Chatbot\Enum\Configuration;
use Ameos\Chatbot\Exception\IaNotSupportedException;
use GuzzleHttp\RequestOptions;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class ChatbotService
{
    private const ENDPOINT_OPENAI = 'https://api.openai.com/v1/chat/completions';
    private const ENDPOINT_MISTRALAI = 'https://api.mistral.ai/v1/chat/completions';

    /**
     * constructor
     * @param RequestFactory $requestFactory
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(
        private readonly RequestFactory $requestFactory,
        private readonly ExtensionConfiguration $extensionConfiguration
    ) {
    }

    /**
     * return endpoint
     *
     * @return string
     */
    private function getEndpoint(): string
    {
        $endpoint = $this->extensionConfiguration->get(
            Configuration::Extension->value,
            Configuration::Endpoint->value
        );

        switch ($endpoint) {
            case Configuration::EndpointOpenAI->value:
                return self::ENDPOINT_OPENAI;
                break;

            case Configuration::EndpointMistralAI->value:
                return self::ENDPOINT_MISTRALAI;
                break;

            default:
                throw new IaNotSupportedException(sprintf('IA %s not supported', $endpoint));
                break;
        }
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
                $this->getEndpoint(),
                'POST',
                [
                    RequestOptions::HEADERS => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $this->extensionConfiguration->get(
                            Configuration::Extension->value,
                            Configuration::ApiKey->value
                        )
                    ],
                    RequestOptions::JSON => [
                        'model' => $this->extensionConfiguration->get(
                            Configuration::Extension->value,
                            Configuration::Model->value
                        ),
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
}
