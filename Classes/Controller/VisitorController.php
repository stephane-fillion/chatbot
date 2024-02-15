<?php

declare(strict_types=1);

namespace Ameos\Chatbot\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

final class VisitorController extends ActionController
{
    /**
     * chatbot question
     *
     * @return ResponseInterface
     */
    public function chatbotAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }
}
