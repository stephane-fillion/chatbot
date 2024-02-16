<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Service;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataGeneratorService
{
    /**
     * construct
     *
     * @param ConfigurationService $configurationService
     */
    public function __construct(private readonly ConfigurationService $configurationService)
    {
    }

    /**
     * generate and return data
     *
     * @param Site $site
     * @param SiteLanguage $siteLanguage
     * @return ?array
     */
    public function generateData(Site $site, SiteLanguage $siteLanguage): ?array
    {
        $fqcn = $this->configurationService->getDataGenerator($siteLanguage);
        if ($fqcn) {
            $generator = GeneralUtility::makeInstance($fqcn);
            return $generator($site);
        }
        return null;
    }
}
