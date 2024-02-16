<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Service\Prompt;

use Ameos\Chatbot\Service\ConfigurationService;
use Ameos\Chatbot\Service\DataGeneratorService;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;

class FrontendPromptService
{
    /**
     * construct
     *
     * @param ConfigurationService $configurationService
     * @param DataGeneratorService $dataGeneratorService
     */
    public function __construct(
        private readonly ConfigurationService $configurationService,
        private readonly DataGeneratorService $dataGeneratorService
    ) {
    }

    /**
     * return system prompt
     *
     * @param Site $site
     * @param SiteLanguage $siteLanguage
     * @return string
     */
    public function getSystemPrompt(Site $site, SiteLanguage $siteLanguage): string
    {
        $systemPrompt = $this->configurationService->getVisitorSystemPrompt($siteLanguage);
        if ($data = $this->dataGeneratorService->generateData($site, $siteLanguage)) {
            $systemPrompt .= ' ' . json_encode($data);
        }
        return $systemPrompt;
    }

    /**
     * return additional user prompt
     *
     * @param SiteLanguage $siteLanguage
     * @return ?string
     */
    public function getUserPrompt(SiteLanguage $siteLanguage): ?string
    {
        return $this->configurationService->getAdditionalUserPrompt($siteLanguage);
    }
}
