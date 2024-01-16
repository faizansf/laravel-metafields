<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Concerns;

use BackedEnum;
use Closure;
use FaizanSf\LaravelMetafields\Facades\MetaCacheHelperFacade;
use FaizanSf\LaravelMetafields\Facades\MetaKeyHelperFacade;
use FaizanSf\LaravelMetafields\Models\Metafield;
use FaizanSf\LaravelMetafields\Proxies\NoCacheMetafieldableProxy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Trait properties to manage caching behavior in a model.
 *
 * @property bool $metafieldCacheEnabled Optional property to be defined in your model.
 *                              When set, it overrides the default caching strategy for the model.
 *                              If true, caching is enabled; if false, caching is disabled.
 * @property int $ttl Optional property to be defined in your model.
 *                    Specifies the time-to-live (TTL) for the cache in seconds.
 *                    Overrides the default cache TTL value for the model.
 *                    Only applicable if caching is enabled.
 */
trait HasMetafields
{
    private ?bool $metafieldCacheEnabled = null;

    private ?int $ttl = null;

    /**
     * The model relationship.
     */
    public function metaFields(): MorphMany
    {
        return $this->morphMany(Metafield::class, config('metafields.model_column_name'));
    }

    /**
     * Creates a new instance of NoCacheMetafieldableProxy to handle metafield operations without caching.
     *
     * This method temporarily disables the caching of metafields for the current object.
     * It achieves this by creating a NoCacheMetafieldableProxy instance, which ensures
     * that all subsequent metafield operations are performed without caching. The original
     * caching setting is preserved and restored after the proxy operation is complete.
     *
     * @return NoCacheMetafieldableProxy Returns a new instance of NoCacheMetafieldableProxy
     *                                   with caching disabled for metafield operations.
     */
    public function withoutCache(): NoCacheMetafieldableProxy
    {
        $originalCacheSetting = $this->getMetafieldCacheEnabled();

        $this->metafieldCacheEnabled = false;

        return new NoCacheMetafieldableProxy($this, $originalCacheSetting);
    }

    /**
     * Retrieves the row associated with the given key from the specified MetaFields model.
     *
     * @param  string|BackedEnum  $key The key to retrieve the row for. Can be either a string or a BackedEnum instance.
     * @return Metafield|null The retrieved row.
     */
    public function getMetaFieldRow(string|BackedEnum $key): ?Metafield
    {
        $key = MetaKeyHelperFacade::normalizeKey($key);

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
        $key = MetaKeyHelperFacade::normalizeKey($key);

        return $this->runCachedOrDirect(
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
        return $this->runCachedOrDirect(
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
        $key = MetaKeyHelperFacade::normalizeKey($key);

        $metafield = $this->metaFields()->updateOrCreate(['key' => $key], ['value' => $value]);

        $this->clearCacheByKey($key);

        $this->clearAllMetafieldsCollectionCache();

        return $metafield;
    }

    public function deleteMetaField(string|BackedEnum $key): bool
    {
        $key = MetaKeyHelperFacade::normalizeKey($key);

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
        MetaCacheHelperFacade::clear($this, $key);
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
        MetaCacheHelperFacade::clear($this);
    }

    public function getMetafieldCacheEnabled(): bool
    {
        return $this->metafieldCacheEnabled ?? config('metafields.cache_metafields');
    }

    public function setMetafieldCacheEnabled(bool $metafieldCacheEnabled): void
    {
        $this->metafieldCacheEnabled = $metafieldCacheEnabled;
    }

    public function getTtl(): int
    {
        return $this->ttl ?? config('metafields.cache_ttl');
    }

    /**
     * Executes the given closure and caches its result for the given time if cache is enabled.
     */
    private function runCachedOrDirect(Closure $callback, ?string $key = null): mixed
    {
        if ($this->canUseCache()) {
            return Cache::remember(
                MetaCacheHelperFacade::getKey($this, $key),
                $this->getTtl(),
                $callback
            );
        }

        return $callback();
    }

    /**
     * Checks if cache is enabled and the current instance is configured to use it.
     */
    private function canUseCache(): bool
    {
        return $this->getMetafieldCacheEnabled();
    }
}
