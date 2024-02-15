<?php

declare(strict_types=1);

use Ameos\Chatbot\Enum\Configuration;

$GLOBALS['SiteConfiguration']['site_language']['columns'][Configuration::VisitorSystemPrompt->value] = [
    'label' => 'System prompt',
    'config' => [
        'type' => 'text',
    ]
];

$GLOBALS['SiteConfiguration']['site_language']['columns'][Configuration::VisitorUserPrompt->value] = [
    'label' => 'Additional user prompt',
    'config' => [
        'type' => 'text',
    ]
];

$GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem'] = str_replace(
    'flag',
    'flag, '
        . Configuration::VisitorSystemPrompt->value . ', '
        . Configuration::VisitorUserPrompt->value . ', ',
    $GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem']
);
