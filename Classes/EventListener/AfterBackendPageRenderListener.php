<?php

declare(strict_types=1);

namespace Ameos\Chatbot\EventListener;

use Ameos\Chatbot\Enum\Configuration;
use TYPO3\CMS\Backend\Controller\Event\AfterBackendPageRenderEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class AfterBackendPageRenderListener
{
    /**
     * constructor
     * @param UriBuilder $uriBuilder
     * @param ExtensionConfiguration $extensionConfiguration
     */
    public function __construct(
        private readonly UriBuilder $uriBuilder,
        private readonly ExtensionConfiguration $extensionConfiguration
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
        $backendActivation = (bool)$this->extensionConfiguration->get(
            Configuration::Extension->value,
            Configuration::BackendActivation->value
        );
        if ($backendActivation) {
            $view = GeneralUtility::makeInstance(StandaloneView::class);
            $view->setTemplatePathAndFilename('EXT:chatbot/Resources/Private/Templates/Chatbot.html');
            $view->assign('chatbotUri', $this->uriBuilder->buildUriFromRoute('ameos_chatbot_question'));

            $event->setContent($event->getContent() . $view->render());
        }
    }
}
