<?php

// merged with vendor/inertiajs/inertia-laravel/config/inertia.php

return [

    'page_paths' => [
        resource_path('js/src/Pages'),
    ],

    'testing' => [

        'ensure_pages_exist' => true,

        'page_paths' => [
            resource_path('js/src/Pages'),
        ],

        'page_extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],
    ],
];
