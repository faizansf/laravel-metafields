<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields;

use BackedEnum;
use Closure;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Utils\CacheContext;
use Illuminate\Support\Facades\Cache;

class LaravelMetafields
{
    protected bool $withCache = true;

    /**
     * Enables the cache temporarily for the current instance.
     *
     * @return self Returns the current instance.
     */
    public function withOutCache(): self
    {
        $this->withCache = false;

        return $this;
    }

    /**
     * Normalizes the given key into a string.
     *
     * @param  string|BackedEnum  $key The key to normalize. Can be either a string or a BackedEnum instance.
     * @return string The normalized key as a string.
     *
     * @throws InvalidKeyException If the key is a BackedEnum instance and its value is not a string.
     */
    public function normalizeKey(string|BackedEnum $key): string
    {
        if ($key instanceof BackedEnum) {
            $value = $key->value;

            if (! is_string($value)) {
                throw InvalidKeyException::withMessage(key: $value);
            }

            return $value;
        }

        return $key;
    }

    /**
     * Clears the cache for the given model and the given key.
     *
     * @param  Metafieldable  $model The model for which to clear the cache.
     * @param  string  $key The key for which to clear the cache.
     */
    public function clearCache(Metafieldable $model, ?string $key = null): void
    {
        Cache::forget($this->getCacheKey($model, $key));
    }

    /**
     * Executes the given closure and caches its result for the given time if cache is enabled.
     *
     * @param  Closure  $callback The closure to execute.
     * @return mixed The result of the executed closure.
     */
    public function runCachedOrDirect(CacheContext $cacheContext, $cacheKey, Closure $callback): mixed
    {
        if ($this->canUseCache($cacheContext)) {
            return Cache::remember(
                $cacheKey,
                $cacheContext->getTtl(),
                $callback
            );
        }

        return $callback();
    }

    /**
     * Generates a cache key for a model's metafield storage, combining a configurable prefix, model's class name,
     * and primary key. The optional key parameter, when provided, specifies a particular metafield; otherwise,
     * it represents all metafields. Null values in model details are substituted with 'null'.
     *
     * @param  Metafieldable  $model The model for which the cache key is being generated.
     * @param  string|null  $key An optional key for a specific metafield. If null, the key represents all metafields.
     * @return string The constructed cache key.
     */
    public function getCacheKey(Metafieldable $model, ?string $key = null): string
    {
        return collect([
            config('metafields.cache_key_prefix'),
            class_basename($model),
            $model->getKey() ?? 'null',
            $key,
        ])->filter(function ($value) {
            return $value !== null;
        })->join(':');
    }

    public function getAllMetaFieldsCacheKey(Metafieldable $model): string
    {
        return $this->getCacheKey($model);
    }

    /**
     * Checks if cache is enabled and the current instance is configured to use it.
     */
    protected function canUseCache(CacheContext $cacheContext): bool
    {
        return $cacheContext->isCacheEnabled() && $this->withCache;
    }
}
