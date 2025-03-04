<?php

namespace Blaspsoft\Onym;

use DateTime;
use Illuminate\Support\Str;

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

    public function __construct()
    {
        $this->strategy = config('onym.strategy');
        $this->options = config('onym.options');
        $this->defaultFilename = config('onym.default_filename', 'file');
        $this->defaultExtension = config('onym.default_extension', 'txt');
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
        ?string $extension = null, 
        ?string $strategy = null, 
        ?array $options = null
    )
    {
        $useStrategy = $strategy ?? $this->strategy;
        $useOptions = $options !== null ? array_merge($this->options, $options) : $this->options;
        $defaultFilename = $defaultFilename ?? $this->defaultFilename;
        $extension = $extension ?? $this->defaultExtension;

        return match ($useStrategy) {
            'random' => $this->random($useOptions['length'] ?? 16, $extension),
            
            'uuid' => $this->uuid($extension),
            
            'timestamp' => $this->timestamp($defaultFilename, $extension, $useOptions),
            
            'date' => $this->date($defaultFilename, $extension, $useOptions),
            
            'prefix' => $this->prefix($defaultFilename, $extension, $useOptions),
            
            'suffix' => $this->suffix($defaultFilename, $extension, $useOptions),
            
            'numbered' => $this->numbered($defaultFilename, $extension, $useOptions),
            
            'slug' => $this->slug($defaultFilename, $extension),
            
            'hash' => $this->hash($defaultFilename, $extension, $useOptions),
            
            default => $defaultFilename . '.' . $extension,
        };
    }

    /**
     * Generate a random string of characters.
     *
     * @param int|null $length The length of the random string.
     * @param string|null $extension The extension of the file.
     * @return string The random string.
     */
    public function random(int $length, string $extension)
    {
        return Str::random($length) . '.' . $extension;
    }

    /**
     * Generate a UUID string.
     *
     * @param string|null $extension The extension of the file.
     * @return string The UUID string.
     */
    public function uuid(string $extension)
    {
        return (string) Str::uuid() . '.' . $extension;
    }

    /**
     * Generate a timestamp string.
     *
     * @param string|null $defaultFilename The original filename.
     * @param string|null $extension The extension of the file.
     * @param array $options The options array.
     * @return string The timestamp string.
     */
    public function timestamp(string $defaultFilename, string $extension, array $options)
    {
        $format = $options['format'] ?? 'Y-m-d_H-i-s';
        $date = new DateTime();
        return $date->format($format) . '_' . $defaultFilename . '.' . $extension;
    }

    /**
     * Generate a date string.
     *
     * @param string|null $defaultFilename The original filename.
     * @param string|null $extension The extension of the file.
     * @param array $options The options array. 
     * @return string The date string.
     */
    public function date(string $defaultFilename, string $extension, array $options)
    {
        $format = $options['format'] ?? 'Y-m-d';
        $date = new DateTime();
        return $date->format($format) . '_' . $defaultFilename . '.' . $extension;
    }

    /**
     * Generate a prefix string.
     *
     * @param string|null $defaultFilename The original filename.
     * @param string|null $extension The extension of the file.
     * @param array $options The options array.
     * @return string The prefix string.    
     */
    public function prefix(string $defaultFilename, string $extension, array $options)
    {
        if (!isset($options['prefix'])) {
            throw new \InvalidArgumentException("The 'prefix' option is required for prefix strategy");
        }
        return $options['prefix'] . $defaultFilename . '.' . $extension;
    }

    /**
     * Generate a suffix string.
     *
     * @param string|null $defaultFilename The original filename.
     * @param string|null $extension The extension of the file.
     * @param array $options The options array.
     * @return string The suffix string.
     */
    public function suffix(string $defaultFilename, string $extension, array $options)
    {
        if (!isset($options['suffix'])) {
            throw new \InvalidArgumentException("The 'suffix' option is required for suffix strategy");
        }
        return $defaultFilename . $options['suffix'] . '.' . $extension;
    }

    /**
     * Generate a numbered string.
     *
     * @param string|null $defaultFilename The original filename.
     * @param string|null $extension The extension of the file.
     * @param array $options The options array.
     * @return string The numbered string.
     */
    public function numbered(string $defaultFilename, string $extension, array $options)
    {
        $number = $options['number'] ?? 1;
        return $defaultFilename . '_' . $number . '.' . $extension;
    }

    /**
     * Generate a slug string.
     *
     * @param string|null $defaultFilename The original filename.
     * @param string|null $extension The extension of the file.
     * @return string The slug string.
     */
    public function slug(string $defaultFilename, string $extension)
    {
        return Str::slug($defaultFilename) . '.' . $extension;
    }

    /**
     * Generate a hash string.
     *
     * @param string|null $defaultFilename The original filename.
     * @param string|null $extension The extension of the file.
     * @param array $options The options array.
     * @return string The hash string.
     */
    public function hash(string $defaultFilename, string $extension, array $options)
    {
        $algorithm = $options['algorithm'] ?? 'sha256';
        if (!in_array($algorithm, hash_algos())) {
            throw new \InvalidArgumentException("Invalid hash algorithm: {$algorithm}");
        }
        $hash = hash($algorithm, $defaultFilename);
        return $hash . '.' . $extension;
    }
}
