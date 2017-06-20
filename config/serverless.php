<?php
return [
    'packaging' => [
        'ignore' => [
            'directories' => [
                'vendor',
                'tests',
                'storage',
                '.idea',
                '.git',
                'resources/lib/php',
                'resources/lib/pkgconfig',
                'resources/dist'
            ],
            'files' => [
                '.gitignore',
                '.env',
                '.env.example',
                '.gitkeep',
                '.htaccess',
                'readme.md',
                'versions.json',
                '.php_cs.cache',
                'composer.json',
                'composer.lock'
            ]
        ]
    ],
    'install' => [
        'php_url' => 'https://github.com/stechstudio/php-lambda/releases/download/1.0.1/php-7.1.6-lambda.tar.gz'
    ]
];
