<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

defined('TYPO3') or die('Access denied');

ExtensionManagementUtility::addStaticFile('chatbot', 'Configuration/TypoScript/', 'Chatbot');
