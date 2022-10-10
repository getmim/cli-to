<?php

return [
    '__name' => 'cli-to',
    '__version' => '0.0.1',
    '__git' => 'git@github.com:getmim/cli-to.git',
    '__license' => 'MIT',
    '__author' => [
        'name' => 'Iqbal Fauzi',
        'email' => 'iqbalfawz@gmail.com',
        'website' => 'http://iqbalfn.com/'
    ],
    '__files' => [
        'modules/cli-to' => ['install','update','remove']
    ],
    '__dependencies' => [
        'required' => [
            [
                'cli' => NULL
            ]
        ],
        'optional' => []
    ],
    'autoload' => [
        'classes' => [
            'CliTo\\Controller' => [
                'type' => 'file',
                'base' => 'modules/cli-to/controller'
            ],
            'CliTo\\Library' => [
                'type' => 'file',
                'base' => 'modules/cli-to/library'
            ]
        ],
        'files' => []
    ],
    'routes' => [
        'tool' => [
            'cliToolToAdd' => [
                'path' => [
                    'value' => 'to add'
                ],
                'handler' => 'CliTo\\Controller\\To::add',
                'method' => 'GET'
            ],
            'cliToolToConnect' => [
                'path' => [
                    'value' => 'to (:name)',
                    'params' => [
                        'name' => 'any'
                    ]
                ],
                'handler' => 'CliTo\\Controller\\To::connect',
                'method' => 'GET'
            ],
            'cliToolToRemove' => [
                'path' => [
                    'value' => 'to remove (:name)',
                    'params' => [
                        'name' => 'any'
                    ]
                ],
                'handler' => 'CliTo\\Controller\\To::remove',
                'method' => 'GET'
            ]
        ]
    ],
    'cli' => [
        'autocomplete' => [
            '!^to remove ([^ ]*)$!' => [
                'priority' => 5,
                'handler' => [
                    'class' => 'CliTo\\Library\\Autocomplete',
                    'method' => 'account'
                ]
            ],
            '!^to( [^ ]*)?$!' => [
                'priority' => 3,
                'handler' => [
                    'class' => 'CliTo\\Library\\Autocomplete',
                    'method' => 'command'
                ]
            ]
        ]
    ],
    'cliTo' => [
        'cipher' => 'aes-128-gcm',
        'secret' => '6cdfb5da73a9bf547be9f75015eea843'
    ]
];
