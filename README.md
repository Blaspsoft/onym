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

```php
// Generate an 8-character random filename
Onym::make(strategy: 'random', options: ['length' => 8]);
Onym::random(8, 'txt');
```

### UUID Strategy

Generates a UUID v4 (universally unique identifier) for the filename. This strategy doesn't accept any options as UUIDs are standardized.

```php
// Generate a UUID filename
Onym::make(strategy: 'uuid');
Onym::uuid('txt');
// Result: "123e4567-e89b-12d3-a456-426614174000.txt"
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

```php
// Using different timestamp formats
Onym::make('document', 'pdf', 'timestamp', ['format' => 'Y-m-d_H-i-s']);
// Result: "2024-03-15_14-30-00_document.pdf"

Onym::make('document', 'pdf', 'timestamp', ['format' => 'YmdHis']);
// Result: "20240315143000_document.pdf"
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

```php
// Using different date formats
Onym::make('document', 'pdf', 'date', ['format' => 'Y-m-d']);
// Result: "2024-03-15_document.pdf"

Onym::make('document', 'pdf', 'date', ['format' => 'Ymd']);
// Result: "20240315_document.pdf"
```

### Numbered Strategy

Adds a number to the filename.

**Options:**

- `number` (int): The number to append to the filename
  - Default: 1
  - Example: `['number' => 5]`

```php
// Adding different numbers
Onym::make('document', 'pdf', 'numbered', ['number' => 5]);
// Result: "document_5.pdf"

Onym::make('document', 'pdf', 'numbered', ['number' => 001]);
// Result: "document_001.pdf"
```

### Slug Strategy

Converts the filename to a URL-friendly slug. This strategy doesn't accept any options as it uses Laravel's `Str::slug()` method with default settings.

```php
// Converting different strings to slugs
Onym::make('My Document Name', 'pdf', 'slug');
// Result: "my-document-name.pdf"

Onym::make('This & That!', 'pdf', 'slug');
// Result: "this-and-that.pdf"
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
  - prefix
  - suffix

```php
// Using different hash algorithms
Onym::make('document', 'pdf', 'hash', ['algorithm' => 'md5']);
// Result: "86985e105f79b95d6bc918fb45ec7727.pdf"

Onym::make('document', 'pdf', 'hash', ['algorithm' => 'sha1']);
// Result: "aaf4c61ddcc5e8a2dabede0f3b482cd9aea9434d.pdf"
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

These defaults can be overridden on a per-call basis using the `options` parameter in the `make()` method.

## License

Blasp is open-sourced software licensed under the [MIT license](LICENSE).
