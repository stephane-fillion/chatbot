<?php

$EM_CONF['chatbot'] = [
    'title'            => 'Chatbot',
    'description'      => 'Chatbot to help TYPO3 contributors in the backend.',
    'author'           => 'Ameos Team',
    'author_company'   => 'Ameos',
    'author_email'     => 'typo3dev@ameos.com',
    'state'            => 'beta',
    'version'          => '1.1.2',
    'constraints'      => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'php'   => '8.1.0-8.2.99',
        ],
        'conflicts' => [],
        'suggests'  => [],
    ],
];
