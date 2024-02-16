<?php

declare(strict_types=1);

use Ameos\Chatbot\Enum\Configuration;

// TODO use LLL

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

$GLOBALS['SiteConfiguration']['site_language']['columns'][Configuration::DataPrompt->value] = [
    'label' => 'Callback for retrieve data for the chatbot',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['value' => '', 'label' => '']
        ]
    ]
];

$GLOBALS['SiteConfiguration']['site_language']['columns'][Configuration::KeepHistory->value] = [
    'label' => 'Send conversation history to AI for new question',
    'config' => [
        'type' => 'check',
        'default' => 1,
    ]
];

$GLOBALS['SiteConfiguration']['site_language']['palettes']['chatbot'] = [
    'label' => 'Chatbot',
    'showitem' => Configuration::VisitorSystemPrompt->value . ', --linebreak--, '
        . Configuration::VisitorUserPrompt->value . ', --linebreak--, '
        . Configuration::DataPrompt->value . ', '
        . Configuration::KeepHistory->value
];

$GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem'] = str_replace(
    'flag',
    'flag, --palette--;;chatbot, ',
    $GLOBALS['SiteConfiguration']['site_language']['types']['1']['showitem']
);
