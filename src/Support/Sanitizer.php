<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Support;

use InvalidArgumentException;

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

    /**
     * Cut a name so that "name.extension" fits in $maxLength bytes, without
     * splitting a multibyte character. Pass an empty extension for a bare name.
     */
    public function limit(string $name, string $extension, int $maxLength): string
    {
        $room = $extension === '' ? $maxLength : $maxLength - strlen($extension) - 1;

        if ($room < 1) {
            throw new InvalidArgumentException(
                "The extension [{$extension}] leaves no room for a name within {$maxLength} bytes"
            );
        }

        if (strlen($name) <= $room) {
            return $name;
        }

        return rtrim(mb_strcut($name, 0, $room, 'UTF-8'), ". \t");
    }
}
