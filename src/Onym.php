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
    public function __construct()
    {
        $this->strategy = config('onym.strategy');
        $this->options = config('onym.options');
    }

    /**
     * Generate a new filename based on the strategy and options.
     *
     * @param string|null $originalFilename The original filename.
     * @param string|null $extension The extension of the file.
     * @param string|null $strategy The strategy to use.
     * @param array|null $options The options to use.
     * @return string The new filename.
     */
    public function make(
        ?string $originalFilename, 
        ?string $extension, 
        ?string $strategy, 
        ?array $options = []
    )
    {
        return match ($strategy) {
            'original' => $originalFilename . '.' . $extension,
            
            'random' => $this->random($options['length'] ?? 16, $extension),
            
            'uuid' => $this->uuid($extension),
            
            'timestamp' => $this->timestamp($originalFilename, $extension, $options),
            
            'date' => $this->date($originalFilename, $extension, $options),
            
            'prefix' => $this->prefix($originalFilename, $extension, $options),
            
            'suffix' => $this->suffix($originalFilename, $extension, $options),
            
            'numbered' => $this->numbered($originalFilename, $extension, $options),
            
            'slug' => $this->slug($originalFilename, $extension),
            
            'hash' => $this->hash($originalFilename, $extension, $options),
            
            default => $originalFilename . '.' . $extension,
        };
    }

    /**
     * Generate a random string of characters.
     *
     * @param int|null $length The length of the random string.
     * @param string $extension The extension of the file.
     * @return string The random string.
     */
    public function random(?int $length = 16, $extension)
    {
        return Str::random($options['length'] ?? 16) . '.' . $extension;
    }

    /**
     * Generate a UUID string.
     *
     * @param string $extension The extension of the file.
     * @return string The UUID string.
     */
    public function uuid($extension)
    {
        return (string) Str::uuid() . '.' . $extension;
    }

    /**
     * Generate a timestamp string.
     *
     * @param string $originalFilename The original filename.
     * @param string $extension The extension of the file.
     * @param array $options The options array.
     * @return string The timestamp string.
     */
    public function timestamp($originalFilename, $extension, $options)
    {
        $format = $options['format'] ?? 'Y-m-d_H-i-s';
        $date = new DateTime();
        return $date->format($format) . '_' . $originalFilename . '.' . $extension;
    }

    /**
     * Generate a date string.
     *
     * @param string $originalFilename The original filename.
     * @param string $extension The extension of the file.
     * @param array $options The options array. 
     * @return string The date string.
     */
    public function date($originalFilename, $extension, $options)
    {
        $format = $options['format'] ?? 'Y-m-d';
        $date = new DateTime();
        return $date->format($format) . '_' . $originalFilename . '.' . $extension;
    }

    /**
     * Generate a prefix string.
     *
     * @param string $originalFilename The original filename.
     * @param string $extension The extension of the file.
     * @param array $options The options array.
     * @return string The prefix string.    
     */
    public function prefix($originalFilename, $extension, $options)
    {
        return ($options['prefix'] ?? 'converted_') . $originalFilename . '.' . $extension;
    }

    /**
     * Generate a suffix string.
     *
     * @param string $originalFilename The original filename.
     * @param string $extension The extension of the file.
     * @param array $options The options array.
     * @return string The suffix string.
     */
    public function suffix($originalFilename, $extension, $options)
    {
        return $originalFilename . ($options['suffix'] ?? '_converted') . '.' . $extension;
    }

    /**
     * Generate a numbered string.
     *
     * @param string $originalFilename The original filename.
     * @param string $extension The extension of the file.
     * @param array $options The options array.
     * @return string The numbered string.
     */
    public function numbered($originalFilename, $extension, $options)
    {
        $number = $options['number'] ?? 1;
        return $originalFilename . '_' . $number . '.' . $extension;
    }

    /**
     * Generate a slug string.
     *
     * @param string $originalFilename The original filename.
     * @param string $extension The extension of the file.
     * @return string The slug string.
     */
    public function slug($originalFilename, $extension)
    {
        return Str::slug($originalFilename) . '.' . $extension;
    }

    /**
     * Generate a hash string.
     *
     * @param string $originalFilename The original filename.
     * @param string $extension The extension of the file.
     * @param array $options The options array.
     * @return string The hash string.
     */
    public function hash($originalFilename, $extension, $options)
    {
        $hash = hash($options['algorithm'] ?? 'sha256', $originalFilename);
        return $hash . '.' . $extension;
    }
}
