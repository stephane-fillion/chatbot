<?php

declare(strict_types=1);

namespace Ameos\Chatbot\EventListener;

use Ameos\Chatbot\Service\ConfigurationService;
use TYPO3\CMS\Backend\Controller\Event\AfterBackendPageRenderEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class AfterBackendPageRenderListener
{
    /**
     * constructor
     * @param UriBuilder $uriBuilder
     * @param ConfigurationService $configurationService
     */
    public function __construct(
        private readonly UriBuilder $uriBuilder,
        private readonly ConfigurationService $configurationService
    ) {
    }

    /**
     * invoke event
     *
     * @param AfterBackendPageRenderEvent $event
     * @return void
     */
    public function __invoke(AfterBackendPageRenderEvent $event): void
    {
        if ($this->configurationService->isEnabledInBackend()) {
            $view = GeneralUtility::makeInstance(StandaloneView::class);
            $view->setTemplatePathAndFilename('EXT:chatbot/Resources/Private/Templates/Contributor/Chatbot.html');
            $view->assign('chatbotUri', $this->uriBuilder->buildUriFromRoute('ameos_chatbot_question'));

            $event->setContent($event->getContent() . $view->render());
        }
    }
}
