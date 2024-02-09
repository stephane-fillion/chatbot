<?php

declare(strict_types=1);

namespace Ameos\Chatbot\EventListener;

use TYPO3\CMS\Backend\Controller\Event\AfterBackendPageRenderEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class AfterBackendPageRenderListener
{
    public function __construct(private readonly UriBuilder $uriBuilder)
    {
    }

    /**
     * invoke event
     *
     * @param AfterBackendPageRenderEvent $event
     * @return void
     */
    public function __invoke(AfterBackendPageRenderEvent $event): void
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename('EXT:chatbot/Resources/Private/Templates/Chatbot.html');
        $view->assign('chatbotUri', $this->uriBuilder->buildUriFromRoute('ameos_chatbot_question'));

        $event->setContent($event->getContent() . $view->render());
    }
}
