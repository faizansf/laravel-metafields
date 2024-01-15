<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Concerns;

use BackedEnum;
use FaizanSf\LaravelMetafields\Facades\CacheHandler;
use FaizanSf\LaravelMetafields\Facades\LaravelMetafields;
use FaizanSf\LaravelMetafields\Models\Metafield;
use FaizanSf\LaravelMetafields\Utils\CacheContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;


/**
 * Trait properties to manage caching behavior in a model.
 *
 * @property bool $cacheEnabled Optional property to be defined in your model.
 *                              When set, it overrides the default caching strategy for the model.
 *                              If true, caching is enabled; if false, caching is disabled.
 *
 * @property int $ttl Optional property to be defined in your model.
 *                    Specifies the time-to-live (TTL) for the cache in seconds.
 *                    Overrides the default cache TTL value for the model.
 *                    Only applicable if caching is enabled.
 */

trait HasMetafields
{
    public static CacheContext $cacheContext;

    public static function bootHasMetaFields(): void
    {
        static::$cacheContext = CacheContext::make(self::class);
    }

    public function initializeHasMetafields(): void
    {
        LaravelMetafields::setModel($this);
    }

    /**
     * The model relationship.
     */
    public function metaFields(): MorphMany
    {
        return $this->morphMany(Metafield::class, config('metafields.model_column_name'));
    }

    /**
     * Retrieves the row associated with the given key from the specified MetaFields model.
     *
     * @param  string|BackedEnum  $key The key to retrieve the row for. Can be either a string or a BackedEnum instance.
     * @return Metafield|null The retrieved row.
     */
    public function getMetaFieldRow(string|BackedEnum $key): ?Metafield
    {
        $key = LaravelMetafields::normalizeKey($key);

        try {
            return $this->metaFields->where('key', $key)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return null;
        }

    }

    /**
     * Retrieves the value associated with the given key attached to this model.
     *
     * @param  string|BackedEnum  $key The key to retrieve the value for. Can be either a string or a BackedEnum instance.
     * @return mixed The retrieved value.
     */
    public function getMetaField(string|BackedEnum $key): mixed
    {
        $key = LaravelMetafields::normalizeKey($key);

        return LaravelMetafields::runCachedOrDirect(
            function () use ($key) {
                $metaField = $this->getMetaFieldRow($key);

                return $metaField->value ?? null;
            }, $key);

    }

    /**
     * Retrieves the values associated with the given keys attached to this model.
     *
     * @param  string|BackedEnum  ...$keys The keys to retrieve the values for. Can be an array of
     * either a string or a BackedEnum instance.
     * @return Collection The retrieved values.
     */
    public function getMetaFields(string|BackedEnum ...$keys): Collection
    {
        return collect($keys)->map(function ($key) {
            return $this->getMetaField($key);
        });
    }

    /**
     * Returns all the meta fields of the model
     */
    public function getAllMetaFields(): Collection
    {
        return LaravelMetafields::runCachedOrDirect(
            function () {
                return $this->metaFields->pluck('value', 'key');
            });
    }

    /**
     * Sets the value associated with the given key in the specified MetaFields model.
     *
     * @param  string|BackedEnum  $key The key to set the value for. Can be either a string or a BackedEnum instance.
     * @param  mixed  $value The value to be set.
     */
    public function setMetaField(string|BackedEnum $key, $value): Metafield
    {
        $key = LaravelMetafields::normalizeKey($key);

        $metafield = $this->metaFields()->updateOrCreate(['key' => $key], ['value' => $value]);

        $this->clearCacheByKey($key);

        $this->clearAllMetafieldsCollectionCache();

        return $metafield;
    }

    public function deleteMetaField(string|BackedEnum $key): bool
    {
        $key = LaravelMetafields::normalizeKey($key);

        $this->getMetaFieldRow($key)?->delete();

        $this->clearCacheByKey($key);

        return true;
    }

    /**
     * Deletes all meta fields of the model
     */
    public function deleteAllMetaFields(): bool
    {
        $metaFieldKeys = $this->metaFields->pluck('key', 'id');

        $this->metaFields()->delete();

        $this->clearCacheByKeys($metaFieldKeys);

        return true;
    }


    /**
     * Clear cache of a single key
     */
    public function clearCacheByKey(string|BackedEnum $key): void
    {
        $key = LaravelMetafields::normalizeKey($key);

        CacheHandler::clear($this, $key);
    }

    /**
     * Clear cache of multiple keys
     */
    public function clearCacheByKeys(array $keys): void
    {
        foreach ($keys as $key) {
            $this->clearCacheByKey($key);
        }
    }

    /**
     * Clear cache of All Metafields Collection
     */
    public function clearAllMetafieldsCollectionCache(): void
    {
        CacheHandler::clear($this);
    }

    /**
     * Retrieves the cache context for the current model
     */
    public function getCacheContext(): CacheContext
    {
        return self::$cacheContext;
    }
}
