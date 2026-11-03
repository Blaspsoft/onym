<?php

declare(strict_types=1);

namespace Blaspsoft\Onym;

use Carbon\CarbonInterface;

/**
 * The inputs every strategy sees for a single call.
 */
final readonly class Context
{
    public function __construct(
        /** The filename exactly as it was passed in. */
        public string $original,
        /** The filename with path separators, control characters and leading dots removed. */
        public string $name,
        /** The cleaned extension, without a leading dot. */
        public string $extension,
        /** The separator option, or the configured default separator. */
        public string $separator,
        /** The configured default filename, for strategies that end up with nothing. */
        public string $fallbackName,
        /** The time of the call, so every piece of one name uses the same moment. */
        public CarbonInterface $now,
    ) {}
}
