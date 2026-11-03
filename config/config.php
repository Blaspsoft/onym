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
    | Onym Default Separator
    |--------------------------------------------------------------------------
    |
    | Placed between the pieces of a name, e.g. the date and the filename.
    | A "separator" option for a strategy overrides it.
    */
    'default_separator' => '_',

    /*
    |--------------------------------------------------------------------------
    | Onym Sanitize
    |--------------------------------------------------------------------------
    |
    | Remove path separators, control characters and leading dots from the
    | whole generated name, including prefixes, suffixes and date formats.
    | Can also be turned off per call with ['sanitize' => false].
    */
    'sanitize' => true,

    /*
    |--------------------------------------------------------------------------
    | Onym Maximum Length
    |--------------------------------------------------------------------------
    |
    | The maximum length of a generated filename in bytes, extension included.
    | Most filesystems allow 255. Longer names are cut, keeping the extension.
    */
    'max_length' => 255,

    /*
    |--------------------------------------------------------------------------
    | Onym Generation Settings
    |--------------------------------------------------------------------------
    |
    | Configure how converted files should be named by default.
    | Available strategies: 'random', 'uuid', 'ulid', 'timestamp', 'date',
    | 'numbered', 'slug', 'hash', plus any registered with Onym::extend()
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
    | - separator: Placed between the pieces of a name (overrides
    |   "default_separator" for that strategy). For slug, the word separator.
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
        | - lowercase: Use lowercase letters only (avoids clashes on
        |   case-insensitive filesystems such as macOS and Windows).
        |
        */
        'random' => [
            'length' => 16,
            'lowercase' => false,
            'prefix' => '',
            'suffix' => '',
        ],

        /*
        |--------------------------------------------------------------------------
        | UUID Strategy Options
        |--------------------------------------------------------------------------
        |
        | Configure the options for the UUID strategy.
        |
        | Available options:
        | - prefix: The prefix of the filename.
        | - suffix: The suffix of the filename.
        |
        */
        'uuid' => [
            'prefix' => '',
            'suffix' => '',
        ],

        /*
        |--------------------------------------------------------------------------
        | ULID Strategy Options
        |--------------------------------------------------------------------------
        |
        | A ULID is unique like a UUID but sorts by creation time.
        |
        | Available options:
        | - lowercase: Lowercase the ULID.
        |
        */
        'ulid' => [
            'lowercase' => false,
            'prefix' => '',
            'suffix' => '',
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
            'prefix' => '',
            'suffix' => '',
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
            'prefix' => '',
            'suffix' => '',
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
        |
        */
        'numbered' => [
            'number' => 1,
            'prefix' => '',
            'suffix' => '',
        ],

        /*
        |--------------------------------------------------------------------------
        | Slug Strategy Options
        |--------------------------------------------------------------------------
        |
        | Configure the options for the slug strategy.
        |
        | Available options:
        | - separator: The separator of the slug.
        |
        */
        'slug' => [
            'separator' => '-',
            'prefix' => '',
            'suffix' => '',
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
        |
        */
        'hash' => [
            'algorithm' => 'md5',
            'prefix' => '',
            'suffix' => '',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Onym Unique Names
    |--------------------------------------------------------------------------
    |
    | Used by Onym::unique(). "disk" is the storage disk to check (null uses
    | the default filesystem disk) and "attempts" is how many names to try
    | before giving up.
    |
    */
    'unique' => [
        'disk' => null,
        'attempts' => 100,
    ],
];
