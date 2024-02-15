<?php

declare(strict_types=1);

use Ameos\Chatbot\Enum\Configuration;

$GLOBALS['SiteConfiguration']['site_language']['columns']['chatbot_system_prompt'] = [
    'label' => Configuration::VisitorSystemPrompt->value,
    'config' => [
        'type' => 'text',
    ]
];

$GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem'] = str_replace(
    'flag',
    'flag, ' . Configuration::VisitorSystemPrompt->value . ', ',
    $GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem']
);
