<?php

return [
    'enabled' => env('BENCHMARK_ENABLED', true),

    'redis' => [
        'prefix' => 'benchmark',
        'connection' => 'default',
    ],

    'events' => [

    ]
];
