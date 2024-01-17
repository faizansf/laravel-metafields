<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Concerns;

use BackedEnum;
use Closure;
use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use FaizanSf\LaravelMetafields\Facades\MetaCacheHelperFacade;
use FaizanSf\LaravelMetafields\Facades\MetaKeyHelperFacade;
use FaizanSf\LaravelMetafields\Models\Metafield;
use FaizanSf\LaravelMetafields\Proxies\NoCacheMetafieldableProxy;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

trait HasMetafields
{
    /**
     * Optional property to be defined in your model. When set, it overrides the default caching strategy for the model.
     * If true, caching is enabled; if false, caching is disabled.
     */
    private ?bool $shouldCacheMetafields = null;

    /**
     * Optional property to be defined in your model. Specifies the time-to-live (TTL) for the cache in seconds.
     * Overrides the default cache TTL value for the model.
     * Only applicable if caching is enabled.
     */
    private ?int $ttl = null;

    /**
     * Overrides the default serialization behaviour of Metafield
     *
     * @var array <string|BackedEnum, ValueSerializer>
     */
    protected array $metafieldSerializers = [];

    /**
     * The model relationship.
     */
    public function metaFields(): MorphMany
    {
        return $this->morphMany(Metafield::class, config('metafields.model_column_name'));
    }

    /**
     * Disables caching for metafield operations.
     *
     * Temporarily switches off caching for the current object by using a NoCacheMetafieldableProxy.
     * The original cache setting is restored after operations via this proxy are completed.
     *
     * @return NoCacheMetafieldableProxy Proxy instance for non-cached metafield operations.
     */
    public function withoutCache(): NoCacheMetafieldableProxy
    {
        $originalCacheSetting = $this->shouldCacheMetafields();

        $this->shouldCacheMetafields = false;

        return new NoCacheMetafieldableProxy($this, $originalCacheSetting);
    }

    /**
     * Retrieves the row associated with the given key from the specified MetaFields model.
     *
     * @param  string|BackedEnum  $key  The key to retrieve the row for. Can be either a string or a BackedEnum instance.
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
     * @param  string|BackedEnum  $key  The key to retrieve the value for. Can be either a string or a BackedEnum instance.
     * @return mixed The retrieved value.
     */
    public function getMetaField(string|BackedEnum $key): mixed
    {
        $key = MetaKeyHelperFacade::normalizeKey($key);

        $serializer = $this->resolveSerializer($key);

        return $this->runCachedOrDirect(fn () => ($metaField = $this->getMetaFieldRow($key)) && isset($metaField->value)
            ? $this->unserialize($metaField->value, $serializer)
            : null, $key);

    }

    /**
     * Retrieves the values associated with the given keys attached to this model.
     *
     * @param  string|BackedEnum  ...$keys  The keys to retrieve the values for. Can be an array of
     *                                      either a string or a BackedEnum instance.
     * @return Collection The retrieved values.
     */
    public function getMetaFields(string|BackedEnum ...$keys): Collection
    {
        //TODO: Implement Query Builder instance of calling getMetaField in loop
        return collect($keys)->map(function ($key) {
            return $this->getMetaField($key);
        });
    }

    /**
     * Returns all the meta fields of the model
     */
    public function getAllMetaFields(): Collection
    {
        return $this->runCachedOrDirect(fn () => $this->metaFields->mapWithKeys(function (Metafield $metafield) {
            $key = $metafield->key;
            $value = $this->unserialize($metafield->value, $this->resolveSerializer($key));

            return [$metafield->key => $value];
        }));
    }

    /**
     * Sets the value associated with the given key in the specified MetaFields model.
     *
     * @param  string|BackedEnum  $key  The key to set the value for. Can be either a string or a BackedEnum instance.
     * @param  mixed  $value  The value to be set.
     */
    public function setMetaField(string|BackedEnum $key, $value): Metafield
    {
        $key = MetaKeyHelperFacade::normalizeKey($key);

        $serializer = $this->resolveSerializer($key);

        $metafield = $this->metaFields()->updateOrCreate(['key' => $key], ['value' => $this->serialize($value, $serializer)]);

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

    public function shouldCacheMetafields(): bool
    {
        return $this->shouldCacheMetafields ?? config('metafields.cache_metafields');
    }

    /**
     * Set Cache Status
     */
    public function setMetafieldCacheStatus(bool $shouldCacheMetafields): void
    {
        $this->shouldCacheMetafields = $shouldCacheMetafields;
    }

    /**
     * Checks if cache is enabled and the current instance is configured to use it.
     */
    private function canUseCache(): bool
    {
        return $this->shouldCacheMetafields();
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
        return $this->canUseCache()
            ? Cache::remember(MetaCacheHelperFacade::getKey($this, $key), $this->getTtl(), $callback)
            : $callback();
    }

    /**
     * Resolves the appropriate serializer for a given key.
     *
     * @param  string|BackedEnum  $key  The key for which to resolve the serializer.
     * @return ValueSerializer|null Returns an instance of the resolved serializer or default serializer.
     */
    private function resolveSerializer(string|BackedEnum $key): ?ValueSerializer
    {
        $key = MetaKeyHelperFacade::normalizeKey($key);

        return ! empty($this->metafieldSerializers[$key])
            ? new $this->metafieldSerializers[$key]
            : App::make(ValueSerializer::class);
    }

    /**
     * Unserializes the given serialized data using the specified serializer.
     *
     * @param  string  $serialized  The serialized data to unserialize.
     * @param  ValueSerializer|null  $serializer  The serializer to use for unserialization.
     * @return mixed Returns unserialized data.
     */
    private function unserialize(string $serialized, ?ValueSerializer $serializer): mixed
    {
        return is_null($serializer)
            ? $serialized
            : $serializer->unserialize($serialized);

    }

    /**
     * Serializes the given value using the specified serializer.
     *
     * @param  mixed  $value  The value to serialize.
     * @param  ValueSerializer|null  $serializer  The serializer to use for serialization.
     * @return string Returns serialized data.
     */
    private function serialize(mixed $value, ?ValueSerializer $serializer): string
    {
        return is_null($serializer)
            ? $value
            : $serializer->serialize($value);
    }
}
