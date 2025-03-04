<p align="center">
    <img src="./.github/assets/icon.png" alt="Onym Icon" width="150" height="150"/>
    <p align="center">
        <a href="https://github.com/Blaspsoft/blasp/actions/workflows/main.yml"><img alt="GitHub Workflow Status (main)" src="https://github.com/Blaspsoft/onym/actions/workflows/main.yml/badge.svg"></a>
        <a href="https://packagist.org/packages/blaspsoft/onym"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/blaspsoft/onym"></a>
        <a href="https://packagist.org/packages/blaspsoft/onym"><img alt="Latest Version" src="https://img.shields.io/packagist/v/blaspsoft/onym"></a>
        <a href="https://packagist.org/packages/blaspsoft/onym"><img alt="License" src="https://img.shields.io/packagist/l/blaspsoft/onym"></a>
    </p>
</p>

# Onym - Flexible Filename Generator

A flexible Laravel package for generating filenames using various strategies and options.

## 🚀 Features

- ✅ **Flexible Filename Generation** – Generate filenames dynamically using various strategies.
- 🎲 **Multiple Strategies** – Supports `random`, `uuid`, `timestamp`, `date`, `numbered`, `slug`, and `hash`.
- 🔧 **Customizable Output** – Specify filename, extension, and additional formatting options.
- 🎯 **Laravel-Friendly** – Designed to work seamlessly with Laravel's filesystem and configuration.
- 📂 **Human-Readable & Unique Names** – Ensures filenames are structured, collision-free, and easy to understand.
- ⚙️ **Configurable Defaults** – Define global settings in `config/onym.php` for consistency across your application.
- 🔌 **Extensible & Developer-Friendly** – Easily add custom filename strategies or modify existing ones.

## Installation

You can install the package via composer:

```bash
composer require blaspsoft/onym
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="onym-config"
```

## Usage

### Available Strategies

### Random Strategy

Generates a random string of characters for the filename.

**Options:**

- `length` (int): The length of the random string
  - Default: 16
  - Example: `['length' => 8]` generates "a1b2c3d4.txt"
- `prefix` (string): String to prepend to the filename
  - Default: ''
  - Example: `['prefix' => 'temp_']` generates "temp_a1b2c3d4.txt"
- `suffix` (string): String to append before the extension
  - Default: ''
  - Example: `['suffix' => '_draft']` generates "a1b2c3d4_draft.txt"

```php
// Generate an 8-character random filename with prefix and suffix
Onym::make(strategy: 'random', options: [
    'length' => 8,
    'prefix' => 'temp_',
    'suffix' => '_draft'
]);
// Result: "temp_a1b2c3d4_draft.txt"

// You can also use the random method directly
Onym::random(string $extension, ?array $options = [])
```

### UUID Strategy

Generates a UUID v4 (universally unique identifier) for the filename.

**Options:**

- `prefix` (string): String to prepend to the filename
  - Default: ''
  - Example: `['prefix' => 'id_']` generates "id_123e4567-e89b-12d3-a456-426614174000.txt"
- `suffix` (string): String to append before the extension
  - Default: ''
  - Example: `['suffix' => '_backup']` generates "123e4567-e89b-12d3-a456-426614174000_backup.txt"

```php
// Generate a UUID filename with prefix and suffix
Onym::make(strategy: 'uuid', options: [
    'prefix' => 'id_',
    'suffix' => '_backup'
]);
// Result: "id_123e4567-e89b-12d3-a456-426614174000_backup.txt"

// You can also use the uuid method directly
Onym::uuid(string $extension, ?array $options = [])
```

### Timestamp Strategy

Adds a timestamp to the filename using PHP's DateTime formatting.

**Options:**

- `format` (string): PHP DateTime format string
  - Default: 'Y-m-d_H-i-s'
  - Common formats:
    - `'Y-m-d_H-i-s'` → "2024-03-15_14-30-00"
    - `'YmdHis'` → "20240315143000"
    - `'U'` → Unix timestamp (e.g., "1710506400")
- `prefix` (string): String to prepend to the filename
  - Default: ''
  - Example: `['prefix' => 'log_']`
- `suffix` (string): String to append before the extension
  - Default: ''
  - Example: `['suffix' => '_archive']`

```php
// Using timestamp with prefix and suffix
Onym::make('document', 'pdf', 'timestamp', [
    'format' => 'Y-m-d_H-i-s',
    'prefix' => 'log_',
    'suffix' => '_archive'
]);
// Result: "log_2024-03-15_14-30-00_document_archive.pdf"

// You can also use the timestamp method directly
Onym::timestamp(string $defaultFilename, string $extension, ?array $options = [])
```

### Date Strategy

Similar to timestamp but focused on date-only formats.

**Options:**

- `format` (string): PHP DateTime format string
  - Default: 'Y-m-d'
  - Common formats:
    - `'Y-m-d'` → "2024-03-15"
    - `'Ymd'` → "20240315"
    - `'Y/m/d'` → "2024/03/15"
- `prefix` (string): String to prepend to the filename
  - Default: ''
  - Example: `['prefix' => 'dated_']`
- `suffix` (string): String to append before the extension
  - Default: ''
  - Example: `['suffix' => '_version']`

```php
// Using date with prefix and suffix
Onym::make('document', 'pdf', 'date', [
    'format' => 'Y-m-d',
    'prefix' => 'dated_',
    'suffix' => '_version'
]);
// Result: "dated_2024-03-15_document_version.pdf"

// You can also use the date method directly
Onym::date(string $defaultFilename, string $extension, ?array $options = [])
```

### Numbered Strategy

Adds a number to the filename.

**Options:**

- `number` (int): The number to append to the filename
  - Default: 1
  - Example: `['number' => 5]`
- `prefix` (string): String to prepend to the filename
  - Default: ''
  - Example: `['prefix' => 'rev_']`
- `suffix` (string): String to append before the extension
  - Default: ''
  - Example: `['suffix' => '_final']`

```php
// Adding numbers with prefix and suffix
Onym::make('document', 'pdf', 'numbered', [
    'number' => 5,
    'prefix' => 'rev_',
    'suffix' => '_final'
]);
// Result: "rev_document_5_final.pdf"

// You can also use the numbered method directly
Onym::numbered(string $defaultFilename, string $extension, ?array $options = [])
```

### Slug Strategy

Converts the filename to a URL-friendly slug.

**Options:**

- `prefix` (string): String to prepend to the filename
  - Default: ''
  - Example: `['prefix' => 'post_']`
- `suffix` (string): String to append before the extension
  - Default: ''
  - Example: `['suffix' => '_draft']`

```php
// Converting strings to slugs with prefix and suffix
Onym::make('My Document Name', 'pdf', 'slug', [
    'prefix' => 'post_',
    'suffix' => '_draft'
]);
// Result: "post_my-document-name_draft.pdf"

// You can also use the slug method directly
Onym::slug(string $defaultFilename, string $extension, ?array $options = [])
```

### Hash Strategy

Generates a hash of the filename using various algorithms.

**Options:**

- `algorithm` (string): The hashing algorithm to use
  - Default: 'md5'
  - Available algorithms:
    - 'md5' (32 characters)
    - 'sha1' (40 characters)
    - 'sha256' (64 characters)
    - Any algorithm supported by PHP's `hash()` function
- `prefix` (string): String to prepend to the filename
  - Default: ''
  - Example: `['prefix' => 'hash_']`
- `suffix` (string): String to append before the extension
  - Default: ''
  - Example: `['suffix' => '_checksum']`

```php
// Using hash with prefix and suffix
Onym::make('document', 'pdf', 'hash', [
    'algorithm' => 'md5',
    'prefix' => 'hash_',
    'suffix' => '_checksum'
]);
// Result: "hash_86985e105f79b95d6bc918fb45ec7727_checksum.pdf"

// You can also use the hash method directly
Onym::hash(string $defaultFilename, string $extension, ?array $options = [])
```

## Global Configuration

You can set default values for all strategies in your `config/onym.php` file:

```php
return [
    // Default filename when none is provided
    'default_filename' => 'file',

    // Default extension when none is provided
    'default_extension' => 'txt',

    // Default strategy when none is specified
    'strategy' => 'random',

    // Default options for all strategies
    'options' => [

        'random' => [
            'length' => 16,
            'prefix' => '',
            'suffix' => '',
        ],

        'timestamp' => [
            'format' => 'Y-m-d_H-i-s',
            'prefix' => '',
            'suffix' => '',
        ],

        'date' => [
            'format' => 'Y-m-d',
            'prefix' => '',
            'suffix' => '',
        ],

        'numbered' => [
            'number' => 1,
            'separator' => '_',
            'prefix' => '',
            'suffix' => '',
        ],

        'hash' => [
            'algorithm' => 'md5',
            'length' => 16,
            'prefix' => '',
            'suffix' => '',
        ],
    ],
];
```

These defaults can be overridden on a per-call basis using the `options` parameter in the `make()` and in all strategy methods.

## License

Blasp is open-sourced software licensed under the [MIT license](LICENSE).
