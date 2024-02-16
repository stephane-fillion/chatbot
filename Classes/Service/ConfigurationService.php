<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Service;

use Ameos\Chatbot\Enum\Configuration;
use Ameos\Chatbot\Exception\IaNotSupportedException;
use Ameos\Chatbot\Interface\DataGeneratorInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class ConfigurationService
{
    private const ENDPOINT_OPENAI = 'https://api.openai.com/v1/chat/completions';
    private const ENDPOINT_MISTRALAI = 'https://api.mistral.ai/v1/chat/completions';

    /**
     * constructor
     *
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(private readonly ExtensionConfiguration $extensionConfiguration)
    {
    }

    /**
     * return true if chatbot is enabled in backend
     *
     * @return bool
     */
    public function isEnabledInBackend(): bool
    {
        return (bool)$this->extensionConfiguration->get(
            Configuration::Extension->value,
            Configuration::BackendActivation->value
        );
    }

    /**
     * return model
     *
     * @return string
     */
    public function getModel(): string
    {
        return $this->extensionConfiguration->get(Configuration::Extension->value, Configuration::Model->value);
    }

    /**
     * return api key
     *
     * @return string
     */
    public function getApiKey(): string
    {
        return $this->extensionConfiguration->get(Configuration::Extension->value, Configuration::ApiKey->value);
    }

    /**
     * return endpoint
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        $configuration = $this->extensionConfiguration->get(
            Configuration::Extension->value,
            Configuration::Endpoint->value
        );

        $endpoint = '';
        switch ($configuration) {
            case Configuration::EndpointOpenAI->value:
                $endpoint = self::ENDPOINT_OPENAI;
                break;

            case Configuration::EndpointMistralAI->value:
                $endpoint = self::ENDPOINT_MISTRALAI;
                break;

            default:
                throw new IaNotSupportedException(sprintf('IA %s not supported', $endpoint));
                break;
        }
        return $endpoint;
    }

    /**
     * return system prompt for site language
     *
     * @return string
     */
    public function getVisitorSystemPrompt(SiteLanguage $siteLanguage): ?string
    {
        $configuration = $siteLanguage->toArray();
        return $configuration[Configuration::VisitorSystemPrompt->value] ?? null;
    }

    /**
     * return addition user prompt for site language
     *
     * @return string
     */
    public function getAdditionalUserPrompt(SiteLanguage $siteLanguage): ?string
    {
        $configuration = $siteLanguage->toArray();
        return $configuration[Configuration::VisitorUserPrompt] ?? null;
    }

    /**
     * return addition user prompt for site language
     *
     * @return string
     */
    public function historyIsKeeped(SiteLanguage $siteLanguage): bool
    {
        $configuration = $siteLanguage->toArray();
        return isset($configuration[Configuration::KeepHistory])
            ? (bool)$configuration[Configuration::KeepHistory] : true;
    }

    /**
     * return data generator FQCN for site language
     *
     * @return string
     */
    public function getDataGenerator(SiteLanguage $siteLanguage): ?string
    {
        $configuration = $siteLanguage->toArray();
        if (!empty($configuration[Configuration::DataPrompt->value])) {
            $dataGeneratorFQCN = $configuration[Configuration::DataPrompt->value];
            if (!class_exists($dataGeneratorFQCN)) {
                // TODO custom exception
                throw new \Exception($dataGeneratorFQCN . ' does not exist');
            }

            if (!in_array(DataGeneratorInterface::class, class_implements($dataGeneratorFQCN))) {
                // TODO custom exception
                throw new \Exception($dataGeneratorFQCN . ' not valid');
            }

            return $dataGeneratorFQCN;
        }
    }

    /**
     * return rate limiter configuration
     *
     * @return array
     */
    public function getRateLimiterConfiguration(): array
    {
        $configuration = $this->extensionConfiguration->get(
            Configuration::Extension->value,
            Configuration::RateLimiter->value
        );
        $configuration['activation'] = (bool)$configuration['activation'];
        $configuration['rate'] = empty($configuration['rate']) ? [] : json_decode($configuration['rate'], true);

        return $configuration;
    }
}
