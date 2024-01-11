<?php

declare(strict_types=1);


namespace FaizanSf\LaravelMetafields;

use BackedEnum;
use Carbon\Carbon;
use Closure;
use FaizanSf\LaravelMetafields\Contracts\MetaFieldable;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Models\MetaField;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;


class LaravelMetafields
{
    protected bool $cacheEnabled;

    protected int $ttl;

    protected bool $temporaryEnableCache = false;

    protected string $cacheKeyPrefix = '';

    /**
     * Use this to enable or disable the cache
     * @param bool $status
     * @return $this
     */
    public function setCacheStatus(bool $status): self
    {
        $this->cacheEnabled = $status;

        return $this;
    }

    /**
     * Set Cache TTL
     * @param int $ttl
     * @return $this
     */
    public function setCacheTtl(int $ttl): self
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Set Cache Key Prefix
     * @param string $prefix
     * @return $this
     */
    public function setCacheKeyPrefix(string $prefix): self
    {
        $this->cacheKeyPrefix = $prefix;

        return $this;
    }

    /**
     * Retrieves the value associated with the given key from the specified MetaFields model.
     *
     * @param MetaFieldable $model The MetaFields model from which to retrieve the value.
     * @param string|BackedEnum $key The key to retrieve the value for. Can be either a string or a BackedEnum instance.
     * @return mixed The retrieved value.
     * @throws InvalidKeyException
     */
    public function getMetaFieldValue(MetaFieldable $model, string|BackedEnum $key): mixed
    {
        return $this->getValue($model,
            $this->normalizeKeyIfEnum($key)
        )->value;
    }

    /**
     * Sets the value for the specified key in the given MetaFields model.
     *
     * @param MetaFieldable $model The MetaFields model in which to set the value.
     * @param string|BackedEnum $key The key for which to set the value. Can be either a string or a BackedEnum instance.
     * @param mixed $value The value to set. It can be of any type.
     * @return MetaField The stored meta field.
     * @throws InvalidKeyException
     */
    public function setMetaFieldValue(MetaFieldable $model, string|BackedEnum $key, mixed $value): MetaField
    {
        $key = $this->normalizeKeyIfEnum($key);

        $metaField = $this->setValue($model, $key, $value);

        if ($this->cacheEnabled) {
            $this->clearCacheByKey($model, $key);
            $this->clearCacheByKey($model, 'all');
        }

        return $metaField;
    }


    /**
     * Retrieves all the meta fields for the given model in key-value format
     *
     * @param MetaFieldable $model
     * @return Collection
     */
    public function getAllMetaFields(MetaFieldable $model): Collection
    {
        return $this->runCachedOrDirect(function() use ($model){
            $model->metaFields->pluck('value', 'key');
        }, $this->getCacheKey($model, 'all'));
    }

    /**
     * Checks if the given Model has meta fields
     *
     * @param MetaFieldable $model
     * @return bool
     */
    public function hasMetafields(MetaFieldable $model): bool
    {
        return $this->getAllMetaFields($model)->count() > 0;
    }

    /**
     * Enables the cache temporarily for the current instance.
     *
     * @return self Returns the current instance.
     */
    public function temporaryEnableCache(): self
    {
        $this->temporaryEnableCache = true;

        return $this;

    }


    /**
     * Disables cache temporarily for the current instance.
     *
     * @return self Returns the current instance of the class.
     */
    public function temporaryDisableCache(): self
    {
        $this->temporaryEnableCache = false;

        return $this;
    }

    /**
     * Checks if caching is enabled.
     *
     * @return bool
     */
    protected function isCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }


    /**
     * Retrieves the value associated with the given key from the specified MetaFields model.
     *
     * @param MetaFieldable $model The MetaFields model from which to retrieve the value.
     * @param string $key The key to retrieve the value for.
     * @return mixed|null The retrieved value, or null if the key is not found.
     */
    protected function getValue(MetaFieldable $model, string $key): mixed
    {
        return $this->runCachedOrDirect(
            function () use ($model, $key) {
                return $model->metaFields->where('key', $key)->first();
            }, $this->getCacheKey($model, $key));
    }


    /**
     * Sets the value associated with the given key in the specified MetaFields model.
     *
     * @param MetaFieldable $model The MetaFields model in which to set the value.
     * @param string $key The key to set the value for.
     * @param mixed $value The value to be set.
     * @return MetaField The newly created MetaFields model.
     */
    protected function setValue(MetaFieldable $model, string $key, mixed $value): MetaField
    {
        return $model->metaFields()->create([
            'key' => $key,
            'value' => $value,
        ]);
    }


    /**
     * Normalizes the given key into a string.
     *
     * @param string|BackedEnum $key The key to normalize. Can be either a string or a BackedEnum instance.
     * @return string The normalized key as a string.
     * @throws InvalidKeyException If the key is a BackedEnum instance and its value is not a string.
     */
    protected function normalizeKeyIfEnum(string|BackedEnum $key): string
    {
        if ($key instanceof BackedEnum) {
            $value = $key->value;

            if (!is_string($value)) {
                throw InvalidKeyException::withMessage(key: $value);
            }

            return $value;
        }

        return $key;
    }

    /**
     * Clears the cache for the given model and the given key.
     *
     * @param MetaFieldable $model The model for which to clear the cache.
     * @param string $key The key for which to clear the cache.
     */
    protected function clearCacheByKey(MetaFieldable $model, string $key): void
    {
        Cache::forget($this->getCacheKey($model, $key));
    }


    /**
     * Constructs the cache key for storing metafields.
     *
     * @param MetaFieldable $model The model instance.
     * @param mixed $key The cache key.
     * @return string The constructed cache key.
     */
    protected function getCacheKey(MetaFieldable $model, string $key): string
    {
        return implode("", [
            $this->cacheKeyPrefix,
            $model::class,
            "::",
            $key
        ]);
    }


    /**
     * Returns the cache time to live.
     * If the model has protected ttl property set then default value will be ignored
     *
     * @return Carbon|null The cache time to live, or null.
     */
    protected function getCacheTtl(MetaFieldable $model = null): Carbon|null
    {
        $ttl = $model && property_exists($model, 'ttl') ? $model->getCacheTtl() : $this->ttl;

        return $ttl ? now()->addMinutes($ttl) : null;
    }


    /**
     * Executes the given closure and caches its result for the given time if cache is enabled.
     *
     * @param Closure $callback The closure to execute.
     * @param string $cacheKey The cache key to use.
     * @param MetaFieldable|null $model
     * @return mixed The result of the executed closure.
     */
    protected function runCachedOrDirect(Closure $callback, string $cacheKey, MetaFieldable $model = null): mixed
    {
        if ($this->isCacheEnabled()) {
            return Cache::remember(
                $cacheKey,
                $this->getCacheTtl($model),
                $callback);
        }

        //execute without cache
        return $callback();
    }

}
