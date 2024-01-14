<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Concerns;

use BackedEnum;

use FaizanSf\LaravelMetafields\Exceptions\MetafieldNotFoundException;
use FaizanSf\LaravelMetafields\Facades\LaravelMetafields;
use FaizanSf\LaravelMetafields\Models\Metafield;
use FaizanSf\LaravelMetafields\Utils\CacheContext;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;


trait HasMetafields
{
    public static CacheContext $cacheContext;

    public static function bootHasMetaFields(): void
    {
        static::$cacheContext = CacheContext::make(self::class);
    }

    /**
     * The model relationship.
     *
     * @return MorphMany
     */
    public function metaFields(): MorphMany
    {
        return $this->morphMany(Metafield::class, config('metafields.model_column_name'));
    }


    /**
     * Retrieves the row associated with the given key from the specified MetaFields model.
     *
     * @param string|BackedEnum $key The key to retrieve the row for. Can be either a string or a BackedEnum instance.
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
     * @param string|BackedEnum $key The key to retrieve the value for. Can be either a string or a BackedEnum instance.
     * @return mixed The retrieved value.
     */
    public function getMetaField(string|BackedEnum $key): mixed
    {
        $key = LaravelMetafields::normalizeKey($key);

        return LaravelMetafields::runCachedOrDirect(
            $this->getCacheContext(),
            LaravelMetafields::getCacheKey($this, $key),
            function () use ($key) {
                $metaField = $this->getMetaFieldRow($key);
                return $metaField->value ?? null;
            });

    }

    /**
     * Retrieves the values associated with the given keys attached to this model.
     *
     * @param string|BackedEnum ...$keys The keys to retrieve the values for. Can be an array of
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
     * @return Collection
     */
    public function getAllMetaFields(): Collection
    {
        return LaravelMetafields::runCachedOrDirect(
            $this->getCacheContext(),
            LaravelMetafields::getAllMetaFieldsCacheKey($this),
            function () {
                return $this->metaFields->pluck('value', 'key');
            });
    }

    /**
     * Sets the value associated with the given key in the specified MetaFields model.
     *
     * @param string|BackedEnum $key The key to set the value for. Can be either a string or a BackedEnum instance.
     * @param mixed $value The value to be set.
     */
    public function setMetaField(string|BackedEnum $key, $value): Metafield
    {
        $key = LaravelMetafields::normalizeKey($key);

        $metafield = $this->metaFields()->updateOrCreate(['key' => $key], ['value' => $value]);

        $this->clearCacheByKey($key);

        $this->clearAllMetafieldsCollectionCache();

        return $metafield;
    }

    /**
     * @param string|BackedEnum $key
     * @return bool
     * @throws MetafieldNotFoundException
     */
    public function deleteMetaField(string|BackedEnum $key): bool
    {
        $key = LaravelMetafields::normalizeKey($key);

        $this->getMetaFieldRow($key)?->delete();

        $this->clearCacheByKey($key);

        return true;
    }

    /**
     * Deletes all meta fields of the model
     * @return bool
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
     * @param string|BackedEnum $key
     * @return void
     */
    public function clearCacheByKey(string|BackedEnum $key): void
    {
        $key = LaravelMetafields::normalizeKey($key);

        LaravelMetafields::clearCache($this, $key);
    }

    /**
     * Clear cache of multiple keys
     * @param string|BackedEnum ...$keys
     * @return void
     */
    public function clearCacheByKeys(string|BackedEnum ...$keys): void
    {
        foreach ($keys as $key) {
            $this->clearCacheByKey($key);
        }
    }

    /**
     * Clear cache of All Metafields Collection
     * @return void
     */
    public function clearAllMetafieldsCollectionCache(): void
    {
        LaravelMetafields::clearCache($this);
    }

    /**
     * Retrieves the cache context for the current model
     * @return CacheContext
     */
    public function getCacheContext(): CacheContext
    {
        return self::$cacheContext;
    }

    public function setCacheContext(CacheContext $context): void
    {
        self::$cacheContext = $context;
    }
}
