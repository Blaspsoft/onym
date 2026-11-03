# Changelog

## 2.0.0

See [UPGRADE.md](UPGRADE.md) for breaking changes.

### Added
- Pipelines: `Onym::pipeline()` combines strategies into one name, and can be registered as a named strategy.
- Custom strategies with `Onym::extend()` and the `Strategy` contract.
- `Onym::unique()` and `Pipeline::unique()` find a name that isn't taken on a storage disk.
- `Onym::fromUpload()` names uploaded files, taking the extension from the detected MIME type.
- The `ulid` strategy, and a `lowercase` option for `random` and `ulid`.
- `Onym::sanitize()` cleans an existing filename.
- `Onym::fake()` with `assertGenerated()`, `assertNotGenerated()`, `assertGeneratedCount()` and `assertNothingGenerated()`.
- `sanitize`, `max_length` and `unique` config keys.

### Changed
- `make()` takes `(filename, extension, strategy, options)`.
- `random()` and `uuid()` take a filename first, like the other strategies.
- The whole generated name is cleaned, including prefixes, suffixes and date formats.
- Names are limited to 255 bytes by default.
- The package config no longer sets a `separator` for `numbered`, so `default_separator` applies to it.
- Requires PHP 8.2+ and Laravel 12+.

### Fixed
- Unknown strategies and invalid options throw instead of producing odd filenames.
- Zero-padded numbers such as `007` keep their padding.

## 1.1.2

- Filenames and extensions passed in are cleaned (`../../.env` gives `env`).
- `slug()` falls back to the default filename when the slug is empty.
- `random()` throws for a length below 1.
- Dates use Carbon, so `Carbon::setTestNow()` works.
- Fixed the README `make()` examples.

## 1.1.1

- Passing `null` as options no longer throws a `TypeError`.

## 1.1.0

- Configurable separator (`default_separator` config and a `separator` option).
- Laravel 13 support.
- `make()` passes options to the `slug` strategy.
