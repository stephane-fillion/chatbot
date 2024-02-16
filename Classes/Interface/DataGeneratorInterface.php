<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Interface;

use TYPO3\CMS\Core\Site\Entity\Site;

interface DataGeneratorInterface
{
    /**
     * invoke generator
     *
     * @param Site $site
     * @return $site
     */
    public function __invoke(Site $site): ?array;
}
