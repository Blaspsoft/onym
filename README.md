# Onym

[![Latest Version on Packagist](https://img.shields.io/packagist/v/your-vendor/onym.svg?style=flat-square)](https://packagist.org/packages/your-vendor/onym)
[![Total Downloads](https://img.shields.io/packagist/dt/your-vendor/onym.svg?style=flat-square)](https://packagist.org/packages/your-vendor/onym)
[![Tests](https://github.com/your-vendor/onym/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/your-vendor/onym/actions/workflows/run-tests.yml)

A flexible Laravel package for generating filenames using various strategies.

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

// Use the make method and overide config values
Onym::make(strategy: 'random', ['random' => 8]); // e.g., "a1b2c3d4.txt"

// Use the random strategy method directly
Onym::random(8, 'txt'); // e.g., "a1b2c3d4.txt"
```
