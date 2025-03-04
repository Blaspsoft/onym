<?php

return [
   
    /*
    |--------------------------------------------------------------------------
    | Onym Default Filename
    |--------------------------------------------------------------------------
    |
    | Configure the default filename to use.
    |
    */
    'default_filename' => 'file',

    /*
    |--------------------------------------------------------------------------
    | Onym Default Extension
    |--------------------------------------------------------------------------
    |
    | Configure the default extension to use.
    */
    'default_extension' => 'txt',

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
    | Configure the options for each strategy.
    | These options are used to generate the new filename.
    |
    | Available options:
    | - length: The length of the random string.
    | - format: The format of the timestamp or date.
    | - prefix: The prefix of the filename.
    | - suffix: The suffix of the filename.
    | - number: The number of the filename.
    | - separator: The separator of the numbered filename.
    | - algorithm: The algorithm of the hash.
    |
    */
    'options' => [

        /*
        |--------------------------------------------------------------------------
        | Random Strategy Options
        |--------------------------------------------------------------------------
        |
        | Configure the options for the random strategy.
        |
        | Available options:
        | - length: The length of the random string.
        |
        */
        'random' => [
            'length' => 16,
        ],

        /*
        |--------------------------------------------------------------------------
        | Timestamp Strategy Options
        |--------------------------------------------------------------------------
        |
        | Configure the options for the timestamp strategy.
        |
        | Available options:
        | - format: The format of the timestamp.
        |
        */
        'timestamp' => [
            'format' => 'Y-m-d_H-i-s',
        ],

        /*
        |--------------------------------------------------------------------------
        | Date Strategy Options
        |--------------------------------------------------------------------------
        |
        | Configure the options for the date strategy.
        |
        | Available options:
        | - format: The format of the date.
        |
        */
        'date' => [
            'format' => 'Y-m-d',
        ],

        /*
        |--------------------------------------------------------------------------
        | Prefix Strategy Options
        |--------------------------------------------------------------------------
        |
        | Configure the options for the prefix strategy.
        |
        | Available options:
        | - prefix: The prefix of the filename.
        |
        */
        'prefix' => [
            'prefix' => 'onym_',
        ],

        /*
        |--------------------------------------------------------------------------
        | Suffix Strategy Options
        |--------------------------------------------------------------------------
        |
        | Configure the options for the suffix strategy.
        |
        | Available options:
        | - suffix: The suffix of the filename.
        |
        */
        'suffix' => [
            'suffix' => '_onym',
        ],

        /*
        |--------------------------------------------------------------------------
        | Numbered Strategy Options
        |--------------------------------------------------------------------------
        |
        | Configure the options for the numbered strategy.
        |
        | Available options:
        | - number: The number of the filename.
        | - separator: The separator of the numbered filename.
        |
        */
        'numbered' => [
            'number' => 1,
            'separator' => '_',
        ],

        /*
        |--------------------------------------------------------------------------
        | Hash Strategy Options
        |--------------------------------------------------------------------------
        |
        | Configure the options for the hash strategy.
        |
        | Available options:
        | - algorithm: The algorithm of the hash.
        | - length: The length of the hash.
        |
        */
        'hash' => [
            'algorithm' => 'md5',
            'length' => 16,
        ],
    ],
];