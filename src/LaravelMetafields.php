<?php

declare(strict_types=1);


namespace FaizanSf\LaravelMetafields;

use BackedEnum;
use FaizanSf\LaravelMetafields\Contracts\MetaFieldable;
use FaizanSf\LaravelMetafields\Contracts\Serializer;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Models\MetaField;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;


class LaravelMetafields
{
    protected bool $cacheEnabled = true;

    protected string $cacheKeyPrefix = '';

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
        );
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

        if($this->cacheEnabled){
            $this->clearCacheByKey($model, $key);
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
        return $model->metaFields->pluck('value', 'key');
    }


    /**
     * Enables the cache for the current instance.
     *
     * @return self Returns the current instance.
     */
    public function enableCache(): self
    {
        $this->cacheEnabled = true;

        return $this;

    }


    /**
     * Disables caching for the current instance.
     *
     * @return self Returns the current instance of the class.
     */
    public function disableCache(): self
    {
        $this->cacheEnabled = false;

        return $this;
    }


    /**
     * Retrieves the value associated with the given key from the specified MetaFields model.
     *
     * @param MetaFieldable $model The MetaFields model from which to retrieve the value.
     * @param mixed $key The key to retrieve the value for.
     * @return mixed|null The retrieved value, or null if the key is not found.
     */
    protected function getValue(MetaFieldable $model, $key): mixed
    {
        return $model->metaFields->first(function ($item, $key) use ($key) {
            return $item->key === $key;
        });
    }


    /**
     * Sets the value associated with the given key in the specified MetaFields model.
     *
     * @param MetaFieldable $model The MetaFields model in which to set the value.
     * @param string $key The key to set the value for.
     * @param mixed $value The value to be set.
     * @return Model The newly created MetaFields model.
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
        $cacheKey = $this->getCacheKey($model, $key);
        Cache::forget($cacheKey);
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
        return config('metafields.cache_key_prefix') . $model::class . "::" . $key;
    }
}
