<p align="center">
    <img src="./.github/assets/icon.png" alt="Onym Icon" width="150" height="150"/>
    <p align="center">
        <a href="https://github.com/Blaspsoft/onym/actions/workflows/main.yml"><img alt="GitHub Workflow Status (main)" src="https://github.com/Blaspsoft/onym/actions/workflows/main.yml/badge.svg"></a>
        <a href="https://packagist.org/packages/blaspsoft/onym"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/blaspsoft/onym"></a>
        <a href="https://packagist.org/packages/blaspsoft/onym"><img alt="Latest Version" src="https://img.shields.io/packagist/v/blaspsoft/onym"></a>
        <a href="https://packagist.org/packages/blaspsoft/onym"><img alt="License" src="https://img.shields.io/packagist/l/blaspsoft/onym"></a>
    </p>
</p>

# Onym

Safe, predictable filenames for Laravel.

```php
use Blaspsoft\Onym\Facades\Onym;

Onym::slug('Q3 Report', 'pdf');                                // q3-report.pdf
Onym::pipeline()->date()->slug()->random(6)->make('Q3 Report', 'pdf'); // 2024-03-15_q3-report_x7k2p9.pdf
Onym::unique('report', 'pdf', 'numbered', directory: 'invoices');      // report_3.pdf if _1 and _2 exist
Onym::fromUpload($request->file('avatar'), 'uuid');            // 9b2f…e1.png, extension from the file's detected type
```

- **Strategies:** `random`, `uuid`, `ulid`, `timestamp`, `date`, `numbered`, `slug`, `hash`, or your own.
- **Pipelines:** combine strategies into one name.
- **Safe output:** path separators, control characters and leading dots are removed, and names are kept within 255 bytes.
- **Unique names:** checked against any Laravel storage disk.
- **Testing:** `Onym::fake()` with assertions.

## Requirements

- PHP 8.2+
- Laravel 12 or 13

Using an older version of Laravel? Onym 1.x supports Laravel 8 to 13. Upgrading from 1.x? See [UPGRADE.md](UPGRADE.md).

## Installation

```bash
composer require blaspsoft/onym
```

To change the defaults, publish the config file:

```bash
php artisan vendor:publish --tag="onym-config"
```

## Usage

Every strategy method takes the original filename, the extension, and an optional array of options:

```php
Onym::slug('Q3 Report', 'pdf', ['prefix' => 'archive_']); // archive_q3-report.pdf
```

`make()` takes the strategy name as its third argument:

```php
Onym::make('Q3 Report', 'pdf', 'slug');
Onym::make(filename: 'Q3 Report', extension: 'pdf', strategy: 'slug', options: ['prefix' => 'archive_']);
```

Leave out the filename, extension or strategy to use the defaults from config (`file`, `txt` and `random`). You can pass the extension with or without a leading dot.

### Strategies

Examples use `Onym::{strategy}('Q3 Report', 'pdf')` at 2024-03-15 14:30:05.

| Strategy | Example | Options |
|---|---|---|
| `random` | `x7k2p9Qa81LmZr4T.pdf` | `length` (16), `lowercase` (false) |
| `uuid` | `9b2f61c4-3f0e-4a8e-b0f5-6f1d2a7c88e1.pdf` | |
| `ulid` | `01JBZ8Q4R6V2K9T3M5N7P8W0XY.pdf` | `lowercase` (false) |
| `timestamp` | `2024-03-15_14-30-05_Q3 Report.pdf` | `format` (`Y-m-d_H-i-s`) |
| `date` | `2024-03-15_Q3 Report.pdf` | `format` (`Y-m-d`) |
| `numbered` | `Q3 Report_1.pdf` | `number` (1) |
| `slug` | `q3-report.pdf` | `separator` (`-`), the word separator |
| `hash` | `56337e9426f8db384e3ebda650723693.pdf` | `algorithm` (`md5`) |

`random`, `uuid` and `ulid` ignore the filename. `ulid` is unique like `uuid`, but sorts by creation time.

Every strategy also accepts:

| Option | Default | |
|---|---|---|
| `prefix` | `''` | Added before the name |
| `suffix` | `''` | Added after the name, before the extension |
| `separator` | `_` | Placed between the pieces of a name, e.g. date and filename |
| `sanitize` | `true` | Clean the whole name (see [Safe filenames](#safe-filenames)) |

Invalid options, such as a `length` of 0 or an unknown hash `algorithm`, throw an `InvalidArgumentException`. So does an unknown strategy name.

### Pipelines

A pipeline joins several strategies into one name:

```php
Onym::pipeline()
    ->date('Ymd')
    ->slug()
    ->random(6)
    ->prefix('inv_')
    ->make('Q3 Report', 'pdf');
// inv_20240315_q3-report_x7k2p9.pdf
```

| Method | |
|---|---|
| `name()` | The original filename, cleaned |
| `slug(?string $separator)` | The original filename as a slug |
| `random(?int $length)`, `uuid()` | Random pieces |
| `timestamp(?string $format)`, `date(?string $format)` | The current time |
| `number(?int $number)` | A number |
| `hash(?string $algorithm)` | A hash of the original filename |
| `add($strategy, array $options = [])` | Any registered strategy, or a `Strategy` instance |
| `separator()`, `prefix()`, `suffix()`, `sanitize()` | Settings for the whole name |
| `make($filename, $extension)` | Generate the name |
| `unique($filename, $extension, $disk, $directory)` | Generate a name that isn't taken (see [Unique names](#unique-names)) |

Each shortcut also accepts a full options array, e.g. `random(['length' => 6, 'lowercase' => true])`.

Pipelines are immutable: every method returns a new pipeline, so you can share a base and extend it:

```php
$report = Onym::pipeline()->date()->slug();

$report->make('Q3 Report', 'pdf');             // 2024-03-15_q3-report.pdf
$report->number(2)->make('Q3 Report', 'pdf');  // 2024-03-15_q3-report_2.pdf
```

### Custom strategies

A strategy returns its piece of the name. Onym adds the prefix, suffix and extension, and cleans the result.

```php
use Blaspsoft\Onym\Context;
use Blaspsoft\Onym\Contracts\Strategy;

class InvoiceNumber implements Strategy
{
    public function part(Context $context, array $options): string
    {
        return 'INV-'.str_pad((string) ($options['number'] ?? 1), 5, '0', STR_PAD_LEFT);
    }
}
```

Register it in a service provider's `boot()` method, either as an instance or as a closure that receives the container:

```php
Onym::extend('invoice', new InvoiceNumber);
Onym::extend('invoice', fn ($app) => $app->make(InvoiceNumber::class));

Onym::make(null, 'pdf', 'invoice', ['number' => 42]); // INV-00042.pdf
Onym::pipeline()->date('Ymd')->add('invoice')->make(null, 'pdf');
```

`Context` gives each strategy:

| Property | |
|---|---|
| `$original` | The filename as it was passed in |
| `$name` | The filename, cleaned |
| `$extension` | The cleaned extension, without a dot |
| `$separator` | The separator for this call |
| `$fallbackName` | The default filename from config |
| `$now` | The time of the call (a Carbon instance), shared by every piece of the name |

A pipeline can also be registered under a name:

```php
Onym::extend('report', Onym::pipeline()->date('Ymd')->slug()->separator('-'));

Onym::make('Q3 Report', 'pdf', 'report'); // 20240315-q3-report.pdf
```

### Unique names

`unique()` returns a name that doesn't exist yet on a storage disk:

```php
Onym::unique('report', 'pdf', 'numbered', directory: 'invoices'); // report_3.pdf if report_1 and report_2 exist
Onym::unique('Q3 Report', 'pdf', 'slug', disk: 's3');             // q3-report-2.pdf if q3-report.pdf exists
Onym::pipeline()->date()->slug()->unique('Q3 Report', 'pdf');     // 2024-03-15_q3-report_2.pdf
```

- `numbered` counts up from its starting number, keeping zero-padding (`009`, then `010`).
- `random`, `uuid`, `ulid`, and pipelines that include one of them, try a new name.
- Every other strategy appends the separator and a counter.

It checks the disk in `onym.unique.disk` (the default disk if that's `null`) and gives up after `onym.unique.attempts` tries (100) with a `RuntimeException`.

> The check happens before you write the file, so two requests at the same moment can still pick the same name. Where that matters, use `random`, `uuid` or `ulid`.

### Uploaded files

`fromUpload()` names an uploaded file from its original name. The extension comes from the file's detected type, not the name the client sent. Only if no type can be detected does it fall back to the client's extension:

```php
Onym::fromUpload($request->file('document'), 'slug');      // "Q3 Report.PDF" → q3-report.pdf
Onym::fromUpload($request->file('avatar'), 'numbered');    // "avatar.php" that is really a PNG → avatar_1.png

$name = Onym::fromUpload($request->file('document'), 'slug', directory: 'documents'); // also makes sure it's free
$request->file('document')->storeAs('documents', $name);
```

### Safe filenames

Onym removes path separators (`/` and `\`), control characters and leading dots:

- **from the filename and extension you pass in**, always: `Onym::numbered('../../.env', 'php')` gives `env_1.php`.
- **from the whole generated name**, unless `sanitize` is off. This covers prefixes, suffixes, date formats and custom strategies: `Onym::date('report', 'pdf', ['format' => 'Y/m/d'])` gives `20240315_report.pdf`.

If you use a prefix as a folder on purpose, turn this off with `'sanitize' => false` in config or per call. It's usually better to pass the folder to `storeAs()` instead.

Names are cut to `max_length` bytes (255), keeping the extension and never splitting a multibyte character.

To clean a filename you already have:

```php
Onym::sanitize('../../My Report.PDF'); // My Report.PDF
Onym::sanitize('.env');                // env
```

### Testing

```php
use Blaspsoft\Onym\Facades\Onym;

Onym::fake(['invoice.pdf', 'receipt.pdf']);

$this->post('/invoices', [...]);

Onym::assertGenerated('invoice.pdf');
Storage::disk('s3')->assertExists('invoices/invoice.pdf');
```

- **Queued names** are returned in order. Once they run out, or if you pass none, Onym generates real names. Every name is recorded either way.
- **Callback:** `Onym::fake(fn ($filename, $extension, $steps) => "{$filename}.{$extension}")` names files with a callback instead. `$steps` lists the strategy names used, such as `['name', 'number']`.

| Assertion | |
|---|---|
| `assertGenerated($name, ?int $times = null)` | `$name` can be a string or a callback that receives each name |
| `assertNotGenerated($name)` | |
| `assertGeneratedCount(int $count)` | |
| `assertNothingGenerated()` | |
| `generated()` | Every name generated, in order |

Date-based names use Carbon, so `Carbon::setTestNow()` freezes them too.

## Configuration

| Key | Default | |
|---|---|---|
| `default_filename` | `file` | Used when no filename is given, or when cleaning leaves nothing |
| `default_extension` | `txt` | Used when no extension is given |
| `default_separator` | `_` | Placed between the pieces of a name, unless a strategy's options set their own `separator` |
| `strategy` | `random` | Used when no strategy is given |
| `sanitize` | `true` | Clean the whole generated name |
| `max_length` | `255` | Maximum filename length in bytes, extension included |
| `options.{strategy}` | | Default options for each strategy, e.g. `options.random.length` |
| `unique.disk` | `null` | Disk checked by `unique()` (`null` uses the default disk) |
| `unique.attempts` | `100` | How many names `unique()` tries |

## License

Onym is open-sourced software licensed under the [MIT license](LICENSE.md).
