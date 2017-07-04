<?php
return [
    'packaging' => [
        'ignore' => [
            // Directories & Fully Qualified Paths
            base_path('vendor'),
            base_path('tests'),
            base_path('storage'),
            base_path('.idea'),
            base_path('.git'),
            base_path('resources/lib/php'),
            base_path('resources/lib/pkgconfig'),
            base_path('resources/dist'),
            // Files Names
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
        ],
        'executables' => [
            'resources/bin/php-cgi'
        ]
    ],
    'install' => [
        'php_url' => 'https://github.com/stechstudio/php-lambda/releases/download/1.0.1/php-7.1.6-lambda.tar.gz'
    ]
];
