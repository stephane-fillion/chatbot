<?php

declare(strict_types=1);

use Ameos\Chatbot\Controller\VisitorController;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die('Access denied');

ExtensionUtility::configurePlugin('Chatbot', 'Visitor', [VisitorController::class => 'chatbot']);
