<?php

declare(strict_types=1);

use Ameos\Chatbot\Enum\Configuration;

$GLOBALS['SiteConfiguration']['site_language']['columns'][Configuration::VisitorSystemPrompt->value] = [
    'label' => 'LLL:EXT:chatbot/Resources/Private/Language/locallang.xlf:visitorSytemPrompt',
    'config' => [
        'type' => 'text',
    ]
];

$GLOBALS['SiteConfiguration']['site_language']['columns'][Configuration::VisitorUserPrompt->value] = [
    'label' => 'LLL:EXT:chatbot/Resources/Private/Language/locallang.xlf:visitorUserPrompt',
    'config' => [
        'type' => 'text',
    ]
];

$GLOBALS['SiteConfiguration']['site_language']['columns'][Configuration::DataPrompt->value] = [
    'label' => 'LLL:EXT:chatbot/Resources/Private/Language/locallang.xlf:dataGenerator',
    'config' => [
        'type' => 'select',
        'renderType' => 'selectSingle',
        'items' => [
            ['value' => '', 'label' => '']
        ]
    ]
];

$GLOBALS['SiteConfiguration']['site_language']['columns'][Configuration::KeepHistory->value] = [
    'label' => 'LLL:EXT:chatbot/Resources/Private/Language/locallang.xlf:keepHistory',
    'config' => [
        'type' => 'check',
        'default' => 1,
    ]
];

$GLOBALS['SiteConfiguration']['site_language']['palettes']['chatbot'] = [
    'label' => 'LLL:EXT:chatbot/Resources/Private/Language/locallang.xlf:chatbot',
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
