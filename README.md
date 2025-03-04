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
- 🎲 **Multiple Strategies** – Supports `random`, `uuid`, `timestamp`, `date`, `prefix`, `suffix`, `numbered`, `slug`, and `hash`.
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

This is the contents of the published config file:

```php
return [
    'default_filename' => 'file',
    'default_extension' => 'txt',
    'strategy' => 'random',
    'options' => [],
];
```

## Usage

```php
use Blaspsoft\Onym;

// Using the default strategy from config
$filename = Onym::make(); // e.g., "abcd1234.txt"

// Specifying a strategy
$filename = Onym::make(strategy: 'uuid'); // e.g., "123e4567-e89b-12d3-a456-426614174000.txt"

// With custom filename and extension
$filename = Onym::make('myfile', 'pdf'); // e.g., "myfile.pdf"

// With custom options
$filename = Onym::make('document', 'pdf', 'prefix', ['prefix' => 'invoice_']); // Result: "invoice_document.pdf"
```

### Available Strategies

#### Random

Generates a random string of characters.

```php
// Use the make method and override config values
Onym::make(strategy: 'random', options: ['length' => 8]); // e.g., "a1b2c3d4.txt"

// Use the random strategy method directly
Onym::random(8, 'txt'); // e.g., "a1b2c3d4.txt"
```

#### UUID

Generates a UUID v4 string.

```php
// Use the make method and override config values
Onym::make(strategy: 'uuid', extension: 'pdf');
// e.g., "123e4567-e89b-12d3-a456-426614174000.pdf"

// Use the uuid strategy method directly
Onym::uuid('pdf');
// e.g., "123e4567-e89b-12d3-a456-426614174000.pdf"
```

#### Timestamp

Adds a timestamp to the filename.

```php
// Use the make method and override config values
Onym::make('document', 'pdf', 'timestamp', ['format' => 'Y-m-d_H-i-s']);
// Result: "2024-03-15_14-30-00_document.pdf"

// Use the timestamp strategy method directly
Onym::timestamp('document', 'pdf', ['format' => 'Y-m-d_H-i-s']);
// Result: "2024-03-15_14-30-00_document.pdf"
```

#### Date

Adds a date to the filename.

```php
// Use the make method and override config values
Onym::make('document', 'pdf', 'date', ['format' => 'Y-m-d']);
// Result: "2024-03-15_document.pdf"

// Use the date strategy method directly
Onym::date('document', 'pdf', ['format' => 'Y-m-d']);
// Result: "2024-03-15_document.pdf"
```

#### Prefix

Adds a prefix to the filename.

```php
// Use the make method and override config values
Onym::make('document', 'pdf', 'prefix', ['prefix' => 'draft_']);
// Result: "draft_document.pdf"

// Use the prefix strategy method directly
Onym::prefix('document', 'pdf', ['prefix' => 'draft_']);
// Result: "draft_document.pdf"
```

#### Suffix

Adds a suffix to the filename.

```php
// Use the make method and override config values
Onym::make('document', 'pdf', 'suffix', ['suffix' => '_v1']);
// Result: "document_v1.pdf"

// Use the suffix strategy method directly
Onym::suffix('document', 'pdf', ['suffix' => '_v1']);
// Result: "document_v1.pdf"
```

#### Numbered

Adds a number to the filename.

```php
// Use the make method and override config values
Onym::make('document', 'pdf', 'numbered', ['number' => 5]);
// Result: "document_5.pdf"

// Use the numbered strategy method directly
Onym::numbered('document', 'pdf', ['number' => 5]);
// Result: "document_5.pdf"
```

#### Slug

Converts the filename to a URL-friendly slug.

```php
// Use the make method and override config values
Onym::make('My Document', 'pdf', 'slug');
// Result: "my-document.pdf"

// Use the slug strategy method directly
Onym::slug('My Document', 'pdf');
// Result: "my-document.pdf"
```

#### Hash

Generates a hash of the filename.

```php
// Use the make method and override config values
Onym::make('document', 'pdf', 'hash', ['algorithm' => 'md5']);
// Result: "86985e105f79b95d6bc918fb45ec7727.pdf"

// Use the hash strategy method directly
Onym::hash('document', 'pdf', ['algorithm' => 'md5']);
// Result: "86985e105f79b95d6bc918fb45ec7727.pdf"
```

## Detailed Strategy Reference

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

### Prefix Strategy

Adds a prefix to the filename. This strategy requires the prefix option to be set.

**Options:**

- `prefix` (string): The string to prepend to the filename
  - Required: Yes
  - Example: `['prefix' => 'draft_']`

```php
// Adding different prefixes
Onym::make('document', 'pdf', 'prefix', ['prefix' => 'draft_']);
// Result: "draft_document.pdf"

Onym::make('document', 'pdf', 'prefix', ['prefix' => 'v1-']);
// Result: "v1-document.pdf"
```

### Suffix Strategy

Adds a suffix to the filename before the extension.

**Options:**

- `suffix` (string): The string to append to the filename
  - Required: Yes
  - Example: `['suffix' => '_v1']`

```php
// Adding different suffixes
Onym::make('document', 'pdf', 'suffix', ['suffix' => '_v1']);
// Result: "document_v1.pdf"

Onym::make('document', 'pdf', 'suffix', ['suffix' => '_draft']);
// Result: "document_draft.pdf"
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
  - Default: 'sha256'
  - Available algorithms:
    - 'md5' (32 characters)
    - 'sha1' (40 characters)
    - 'sha256' (64 characters)
    - Any algorithm supported by PHP's `hash()` function

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
    // Default strategy when none is specified
    'strategy' => 'random',

    // Default options for all strategies
    'options' => [
        'length' => 16,           // for random strategy
        'format' => 'Y-m-d_H-i-s', // for timestamp strategy
        'prefix' => 'pre_',       // for prefix strategy
        'suffix' => '_v1',        // for suffix strategy
        'number' => 1,            // for numbered strategy
        'algorithm' => 'sha256',  // for hash strategy
    ],

    // Default filename when none is provided
    'default_filename' => 'file',

    // Default extension when none is provided
    'default_extension' => 'txt',
];
```

These defaults can be overridden on a per-call basis using the `options` parameter in the `make()` method.
