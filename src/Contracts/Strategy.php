<?php

declare(strict_types=1);

namespace Blaspsoft\Onym\Contracts;

use Blaspsoft\Onym\Context;

interface Strategy
{
    /**
     * Return this strategy's piece of the filename.
     *
     * Return only the piece itself: Onym joins the pieces and adds the
     * prefix, suffix and extension.
     *
     * @param  array<string, mixed>  $options
     */
    public function part(Context $context, array $options): string;
}
