<?php

declare(strict_types=1);

namespace Blaspsoft\Onym;

use Blaspsoft\Onym\Contracts\Strategy;
use Blaspsoft\Onym\Strategies\DateStrategy;
use Blaspsoft\Onym\Strategies\HashStrategy;
use Blaspsoft\Onym\Strategies\NameStrategy;
use Blaspsoft\Onym\Strategies\NumberStrategy;
use Blaspsoft\Onym\Strategies\RandomStrategy;
use Blaspsoft\Onym\Strategies\SlugStrategy;
use Blaspsoft\Onym\Strategies\TimestampStrategy;
use Blaspsoft\Onym\Strategies\UlidStrategy;
use Blaspsoft\Onym\Strategies\UuidStrategy;
use Closure;
use Illuminate\Support\Manager;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Resolves strategy names to Strategy instances.
 *
 * Strategies are stateless, so each one is built once and reused.
 */
class OnymManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return (string) $this->config->get('onym.strategy', 'random');
    }

    public function strategy(string $name): Strategy
    {
        $strategy = $this->driver($name);

        if (! $strategy instanceof Strategy) {
            throw new InvalidArgumentException(
                "Onym strategy [{$name}] must implement ".Strategy::class.'.'
            );
        }

        return $strategy;
    }

    public function has(string $name): bool
    {
        return isset($this->customCreators[$name])
            || method_exists($this, 'create'.Str::studly($name).'Driver');
    }

    /**
     * Register a custom strategy, replacing any instance already built under that name.
     *
     * @param  string  $driver
     */
    public function extend($driver, Closure $callback): static
    {
        unset($this->drivers[$driver]);

        return parent::extend($driver, $callback);
    }

    /**
     * @param  string  $driver
     */
    protected function createDriver($driver): mixed
    {
        if (! $this->has($driver)) {
            throw new InvalidArgumentException("Unknown Onym strategy [{$driver}].");
        }

        return parent::createDriver($driver);
    }

    protected function createRandomDriver(): Strategy
    {
        return new RandomStrategy;
    }

    protected function createUuidDriver(): Strategy
    {
        return new UuidStrategy;
    }

    protected function createUlidDriver(): Strategy
    {
        return new UlidStrategy;
    }

    protected function createTimestampDriver(): Strategy
    {
        return new TimestampStrategy;
    }

    protected function createDateDriver(): Strategy
    {
        return new DateStrategy;
    }

    protected function createNumberDriver(): Strategy
    {
        return new NumberStrategy;
    }

    protected function createSlugDriver(): Strategy
    {
        return new SlugStrategy;
    }

    protected function createHashDriver(): Strategy
    {
        return new HashStrategy;
    }

    protected function createNameDriver(): Strategy
    {
        return new NameStrategy;
    }
}
