<?php

use Visualsoft\Monitoring\Http\Middleware\TrustVisualsoftMonitoring;

return [
    'middleware' => [
        'web',
        TrustVisualsoftMonitoring::class,
    ],
];
