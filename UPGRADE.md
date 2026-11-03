# Upgrading from 1.x to 2.0

For the same inputs, most calls give exactly the same filenames as 1.x. The changes below are mostly to how you call Onym. The ones most likely to affect you are marked **High**.

## Requirements

**High.** Onym 2.0 requires PHP 8.2+ and Laravel 12 or 13. Stay on 1.x for older versions; it still gets bug fixes.

```bash
composer require blaspsoft/onym:^2.0
```

## `make()` argument order

**High.** The extension now comes before the strategy, matching the strategy methods:

```php
// 1.x
Onym::make('report', 'slug', 'pdf', $options);

// 2.0
Onym::make('report', 'pdf', 'slug', $options);
```

Calls that use named arguments for `strategy`, `extension` and `options` keep working. The first parameter was renamed from `$defaultFilename` to `$filename`, so a named `defaultFilename:` becomes `filename:`.

## `random()` and `uuid()` take a filename first

**High.** Every strategy method now takes `(filename, extension, options)`. Pass `null` as the filename for `random()` and `uuid()`, which ignore it:

```php
// 1.x
Onym::random('pdf', ['length' => 8]);
Onym::uuid('pdf');

// 2.0
Onym::random(null, 'pdf', ['length' => 8]);
Onym::uuid(null, 'pdf');
```

## Unknown strategies throw

**Medium.** 1.x returned `filename.extension` for a strategy name it didn't know, so a typo went unnoticed. 2.0 throws an `InvalidArgumentException`. Check the `strategy` value in a published `config/onym.php` too.

## Invalid options throw

**Medium.** These now throw an `InvalidArgumentException` instead of giving odd names:

- a `random` `length` below 1 or not a whole number
- a negative or non-numeric `number` (digit strings like `'007'` are still fine)
- an empty or non-string date `format`
- an unknown hash `algorithm`
- an array, object or boolean `prefix`, `suffix` or `separator`

## The whole name is cleaned

**Medium.** 1.x cleaned the filename and extension you passed in. 2.0 also cleans the generated name as a whole, so path separators, control characters and leading dots are removed from prefixes, suffixes and date formats too:

```php
Onym::numbered('report', 'pdf', ['prefix' => 'invoices/']); // 1.x: invoices/report_1.pdf   2.0: invoicesreport_1.pdf
Onym::date('report', 'pdf', ['format' => 'Y/m/d']);         // 1.x: 2024/03/15_report.pdf   2.0: 20240315_report.pdf
```

If you rely on this to build folder paths, pass the folder to `storeAs()` instead, or turn cleaning off with `'sanitize' => false` per call or in config.

## Names are limited to 255 bytes

**Low.** Longer names are cut to fit, keeping the extension. Change this with the new `max_length` config key.

## Creating `Onym` yourself

**Low.** The 1.x constructor (`new Onym($options, $separator)`) and the public `$strategy`, `$options`, `$defaultFilename`, `$defaultExtension` and `$defaultSeparator` properties are gone. Use the facade, or resolve `Blaspsoft\Onym\Onym` from the container, and set defaults in config:

```php
// 1.x
$onym = new Onym([], '-');

// 2.0
config(['onym.default_separator' => '-']);
$onym = app(\Blaspsoft\Onym\Onym::class);
```

## Config

A published 1.x config file keeps working: the new keys (`sanitize`, `max_length`, `unique`, `options.ulid`) fall back to their defaults. To see them, compare with the package's `config/config.php`, or publish again with `--force` after saving your changes.

The 1.x config set `'separator' => '_'` under `options.numbered`, which overrides `default_separator` for numbered names. 2.0's config leaves it out. If you've published the config and want `default_separator` to apply to numbered names too, remove that line.

## New in 2.0

These need no changes, but are worth knowing about:

- **Pipelines** that combine strategies: `Onym::pipeline()->date()->slug()->random(6)->make('Q3 Report', 'pdf')`
- **Custom strategies** with `Onym::extend()`
- **`Onym::unique()`**, which checks a storage disk for a free name
- **`Onym::fromUpload()`**, which takes the extension from the file's detected type (falling back to the client's extension if none is detected)
- **The `ulid` strategy**, and a `lowercase` option for `random` and `ulid`
- **`Onym::sanitize()`** for filenames you already have
- **`Onym::fake()`** and assertions for tests

See the [README](README.md) for details.
