<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Support;

class Sanitizer
{
    /**
     * Strip path separators, control characters and leading dots from a
     * caller-supplied filename so it cannot escape a directory or become hidden.
     */
    public function filename(string $filename, string $fallback): string
    {
        $filename = preg_replace('/[\x00-\x1F\x7F\/\\\\]/u', '', $filename) ?? '';
        $filename = ltrim(trim($filename), '.');

        return $filename === '' ? $fallback : $filename;
    }

    /**
     * Reduce an extension to letters, digits and single inner dots
     * (so "tar.gz" survives but ".pdf" and "txt/../x" do not).
     */
    public function extension(string $extension, string $fallback): string
    {
        $extension = preg_replace('/[^A-Za-z0-9.]/', '', $extension) ?? '';
        $extension = trim(preg_replace('/\.{2,}/', '.', $extension) ?? '', '.');

        return $extension === '' ? $fallback : $extension;
    }
}
