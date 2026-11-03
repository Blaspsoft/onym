<?php

namespace Blaspsoft\Onym;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;

class Onym
{
    /**
     * The strategy to use.
     *
     * @var string
     */
    public string $strategy;

    /**
     * The options to use.
     *
     * @var array
     */
    public array $options;

    /**
     * The default filename to use.
     *
     * @var string
     */
    public string $defaultFilename;

    /**
     * The default extension to use.
     *
     * @var string
     */
    public string $defaultExtension;

    /**
     * The default separator to use.
     *
     * @var string
     */
    public string $defaultSeparator;

    public function __construct(array $options = [], ?string $defaultSeparator = null)
    {
        $this->strategy = config('onym.strategy', 'random');
        $this->options = $options ?: config('onym.options', []);
        $this->defaultFilename = config('onym.default_filename', 'file');
        $this->defaultExtension = config('onym.default_extension', 'txt');
        $this->defaultSeparator = $defaultSeparator ?? config('onym.default_separator', '_');
    }

    /**
     * Generate a new filename based on the strategy and options.
     *
     * @param string|null $defaultFilename The original filename.
     * @param string|null $extension The extension of the file.
     * @param string|null $strategy The strategy to use (falls back to config value if null).
     * @param array|null $options The options to use (merges with config options if provided).
     * @return string The new filename.
     */
    public function make(
        ?string $defaultFilename = null, 
        ?string $strategy = null,
        ?string $extension = null,  
        ?array $options = null
    )
    {
        $useStrategy = $strategy ?? $this->strategy;
        $useOptions = $options !== null ? $this->mergeOptions($options, $useStrategy, $this->options) : $this->options[$useStrategy] ?? [];
        $defaultFilename = $defaultFilename ?? $this->defaultFilename;
        $extension = $extension ?? $this->defaultExtension;

        return match ($useStrategy) {
            'random' => $this->random($extension, $useOptions),
            
            'uuid' => $this->uuid($extension, $useOptions),
            
            'timestamp' => $this->timestamp($defaultFilename, $extension, $useOptions),
            
            'date' => $this->date($defaultFilename, $extension, $useOptions),
            
            'numbered' => $this->numbered($defaultFilename, $extension, $useOptions),
            
            'slug' => $this->slug($defaultFilename, $extension, $useOptions),
            
            'hash' => $this->hash($defaultFilename, $extension, $useOptions),
            
            default => $this->cleanFilename($defaultFilename) . '.' . $this->cleanExtension($extension),
        };
    }

    /**
     * Generate a random string of characters.
     *
     * @param string $extension The extension of the file.
     * @param array|null $options The options array.
     * @return string The random string.
     * @throws \InvalidArgumentException If the length is less than 1.
     */
    public function random(string $extension, ?array $options = [])
    {
        $options = $this->mergeOptions($options, 'random', $this->options);
        $length = (int) ($options['length'] ?? 16);

        if ($length < 1) {
            throw new InvalidArgumentException("Random length must be at least 1, {$length} given");
        }

        $filename = Str::random($length);
        return $this->applyAffixes($filename, $extension, $options);
    }

    /**
     * Generate a UUID string.
     *
     * @param string $extension The extension of the file.
     * @param array|null $options The options array.
     * @return string The UUID string.
     */
    public function uuid(string $extension, ?array $options = [])
    {
        $options = $this->mergeOptions($options, 'uuid', $this->options);
        $filename = (string) Str::uuid();
        return $this->applyAffixes($filename, $extension, $options);
    }

    /**
     * Generate a timestamp string.
     *
     * @param string $defaultFilename The original filename.
     * @param string $extension The extension of the file.
     * @param array|null $options The options array.
     * @return string The timestamp string.
     */
    public function timestamp(string $defaultFilename, string $extension, ?array $options = [])
    {
        $options = $this->mergeOptions($options, 'timestamp', $this->options);

        $format = $options['format'] ?? 'Y-m-d_H-i-s';
        $separator = $options['separator'] ?? $this->defaultSeparator;
        $filename = Carbon::now()->format($format) . $separator . $this->cleanFilename($defaultFilename);
        return $this->applyAffixes($filename, $extension, $options);
    }

    /**
     * Generate a date string.
     *
     * @param string $defaultFilename The original filename.
     * @param string $extension The extension of the file.
     * @param array|null $options The options array.
     * @return string The date string.
     */
    public function date(string $defaultFilename, string $extension, ?array $options = [])
    {
        $options = $this->mergeOptions($options, 'date', $this->options);
        $format = $options['format'] ?? 'Y-m-d';
        $separator = $options['separator'] ?? $this->defaultSeparator;
        $filename = Carbon::now()->format($format) . $separator . $this->cleanFilename($defaultFilename);
        return $this->applyAffixes($filename, $extension, $options);
    }

    /**
     * Generate a numbered string.
     *
     * @param string $defaultFilename The original filename.
     * @param string $extension The extension of the file.
     * @param array|null $options The options array.
     * @return string The numbered string.
     */
    public function numbered(string $defaultFilename, string $extension, ?array $options = [])
    {
        $options = $this->mergeOptions($options, 'numbered', $this->options);
        $number = $options['number'] ?? 1;
        $separator = $options['separator'] ?? $this->defaultSeparator;
        $filename = $this->cleanFilename($defaultFilename) . $separator . $number;
        return $this->applyAffixes($filename, $extension, $options);
    }

    /**
     * Generate a slug string.
     *
     * @param string $defaultFilename The original filename.
     * @param string $extension The extension of the file.
     * @param array|null $options The options array.
     * @return string The slug string.
     */
    public function slug(string $defaultFilename, string $extension, ?array $options = [])
    {
        $options = $this->mergeOptions($options, 'slug', $this->options);
        $separator = $options['separator'] ?? $this->defaultSeparator;
        $filename = Str::slug($defaultFilename, $separator);

        // Names with nothing to slug (e.g. "!!!" or non-Latin text) fall back to the default filename.
        if ($filename === '') {
            $filename = Str::slug($this->defaultFilename, $separator) ?: 'file';
        }

        return $this->applyAffixes($filename, $extension, $options);
    }

    /**
     * Generate a hash string.
     *
     * @param string $defaultFilename The original filename.
     * @param string $extension The extension of the file.
     * @param array|null $options The options array.
     * @return string The hash string.
     */
    public function hash(string $defaultFilename, string $extension, ?array $options = [])
    {
        $options = $this->mergeOptions($options, 'hash', $this->options);
        $algorithm = $options['algorithm'] ?? 'md5';

        if (!in_array($algorithm, hash_algos())) {
            throw new \InvalidArgumentException("Invalid hash algorithm: {$algorithm}");
        }

        $hash = hash($algorithm, $defaultFilename);
        $filename = $hash;
        return $this->applyAffixes($filename, $extension, $options);
    }

    /**
     * Merge options with the default options.
     *
     * @param array|null $options The options to merge.
     * @param string $strategy The strategy being used.
     * @param array $defaultOptions The default options.
     * @return array The merged options.
     */
    private function mergeOptions(?array $options, string $strategy, array $defaultOptions): array
    {
        $strategyOptions = $defaultOptions[$strategy] ?? [];
        return array_merge($strategyOptions, $options ?? []);
    }

    /**
     * Strip path separators, control characters and leading dots from a
     * caller-supplied filename so it cannot escape a directory or become hidden.
     *
     * @param string $filename
     * @return string
     */
    private function cleanFilename(string $filename): string
    {
        $filename = preg_replace('/[\x00-\x1F\x7F\/\\\\]/u', '', $filename) ?? '';
        $filename = ltrim(trim($filename), '.');

        return $filename === '' ? $this->defaultFilename : $filename;
    }

    /**
     * Reduce an extension to letters, digits and single inner dots
     * (so "tar.gz" survives but ".pdf" and "txt/../x" do not).
     *
     * @param string $extension
     * @return string
     */
    private function cleanExtension(string $extension): string
    {
        $extension = preg_replace('/[^A-Za-z0-9.]/', '', $extension) ?? '';
        $extension = trim(preg_replace('/\.{2,}/', '.', $extension) ?? '', '.');

        return $extension === '' ? $this->defaultExtension : $extension;
    }

    /**
     * Apply prefix and suffix to filename
     *
     * @param string $filename
     * @param string $extension
     * @param array|null $options
     * @return string
     */
    private function applyAffixes(string $filename, string $extension, ?array $options = []): string
    {
        if (isset($options['prefix'])) {
            $filename = $options['prefix'] . $filename;
        }
        if (isset($options['suffix'])) {
            $filename = $filename . $options['suffix'];
        }
        return $filename . '.' . $this->cleanExtension($extension);
    }
}
