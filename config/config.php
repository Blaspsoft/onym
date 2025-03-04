<?php

return [
   
    /*
    |--------------------------------------------------------------------------
    | Onym Generation Settings
    |--------------------------------------------------------------------------
    |
    | Configure how converted files should be named by default.
    | Available strategies: 'original', 'random', 'uuid', 'timestamp', 'date',
    | 'prefix', 'suffix', 'numbered', 'slug', 'hash'
    |
    */
    'strategy' => 'random',

    
    /*
    |--------------------------------------------------------------------------
    | Onym Generation Options
    |--------------------------------------------------------------------------
    |
    | Configure how converted files should be named by default.
    | Available strategies: 'original', 'random', 'uuid', 'timestamp', 'date',
    | 'prefix', 'suffix', 'numbered', 'slug', 'hash'
    |
    */
    'options' => [
        'random' => [
            'length' => 16,
        ],
        'timestamp' => [
            'format' => 'Y-m-d_H-i-s',
        ],
        'date' => [
            'format' => 'Y-m-d',
        ],
        'prefix' => [
            'prefix' => 'converted_',
        ],
        'suffix' => [
            'suffix' => '_converted',
        ],
        'numbered' => [
            'number' => 1,
            'separator' => '_',
        ],
        'hash' => [
            'algorithm' => 'md5',
            'length' => 16,
        ],
    ],
];