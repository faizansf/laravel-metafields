<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields;

use BackedEnum;
use Closure;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Utils\CacheContext;
use FaizanSf\LaravelMetafields\Utils\CacheHandler;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class LaravelMetafields
{
    private bool $withCache = true;

    private Metafieldable $model;

    /**
     * @return $this
     */
    public function setModel(Metafieldable $model): LaravelMetafields
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Enables the cache temporarily for the current instance.
     *
     * @return LaravelMetafields Returns the current instance.
     */
    public function withOutCache(): LaravelMetafields
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

            if (! $this->isValidKey($value)) {
                throw InvalidKeyException::withMessage(key: $value);
            }

            return $value;
        }

        return $key;
    }

    public function normalizeKeys(array $keys): array
    {
        return Arr::map($keys, function ($key) {
            return $this->normalizeKey($key);
        });
    }

    /**
     * Executes the given closure and caches its result for the given time if cache is enabled.
     */
    public function runCachedOrDirect(Closure $callback, ?string $key = null): mixed
    {
        if ($this->canUseCache($this->model->getCacheContext())) {
            return Cache::remember(
                CacheHandler::getKey($this->model, $key),
                $this->model->getCacheContext()->getTtl(),
                $callback
            );
        }

        return $callback();
    }

    /**
     * Checks if cache is enabled and the current instance is configured to use it.
     */
    private function canUseCache(CacheContext $cacheContext): bool
    {
        return $cacheContext->isCacheEnabled() && $this->withCache;
    }

    /**
     * Checks if the given key is a valid key for a metafield.
     *
     * @param  mixed  $key The key to check.
     * @return bool True if the key is valid, false otherwise.
     */
    private function isValidKey(mixed $key): bool
    {
        return is_string($key);
    }
}
