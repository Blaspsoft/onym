<?php

namespace Blaspsoft\Onym;

use DateTime;
use Illuminate\Support\Str;


class Onym
{
    public string $strategy;

    public array $options;

    public function __construct()
    {
        $this->strategy = config('onym.strategy');
        $this->options = config('onym.options');
    }

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

    public function random(?int $length = 16, $extension)
    {
        return Str::random($options['length'] ?? 16) . '.' . $extension;
    }

    public function uuid($extension)
    {
        return (string) Str::uuid() . '.' . $extension;
    }

    public function timestamp($originalFilename, $extension, $options)
    {
        $format = $options['format'] ?? 'Y-m-d_H-i-s';
        $date = new DateTime();
        return $date->format($format) . '_' . $originalFilename . '.' . $extension;
    }

    public function date($originalFilename, $extension, $options)
    {
        $format = $options['format'] ?? 'Y-m-d';
        $date = new DateTime();
        return $date->format($format) . '_' . $originalFilename . '.' . $extension;
    }

    public function prefix($originalFilename, $extension, $options)
    {
        return ($options['prefix'] ?? 'converted_') . $originalFilename . '.' . $extension;
    }

    public function suffix($originalFilename, $extension, $options)
    {
        return $originalFilename . ($options['suffix'] ?? '_converted') . '.' . $extension;
    }

    public function numbered($originalFilename, $extension, $options)
    {
        $number = $options['number'] ?? 1;
        return $originalFilename . '_' . $number . '.' . $extension;
    }

    public function slug($originalFilename, $extension)
    {
        return Str::slug($originalFilename) . '.' . $extension;
    }

    public function hash($originalFilename, $extension, $options)
    {
        $hash = hash($options['algorithm'] ?? 'sha256', $originalFilename);
        return $hash . '.' . $extension;
    }
}
