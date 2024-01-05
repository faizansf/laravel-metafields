<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields;

use BackedEnum;
use FaizanSf\LaravelMetafields\Contracts\MetaFields;
use FaizanSf\LaravelMetafields\Contracts\Serializer;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use Illuminate\Database\Eloquent\Model;

class LaravelMetafields
{
    protected bool $cacheEnabled = true;

    protected string $cacheKeyPrefix = '';

    public function __construct(
        protected Serializer $serializer,
    ) {
    }

    /**
     * Retrieves and unserializes the value associated with the given key from the specified MetaFields model.
     *
     * @param  MetaFields  $model The MetaFields model from which to retrieve the value.
     * @param  string|BackedEnum  $key The key to retrieve the value for. Can be either a string or a BackedEnum instance.
     * @return mixed The retrieved value.
     */
    public function getMetaFieldValue(MetaFields $model, string|BackedEnum $key): mixed
    {
        return $this->unserialize(
            $this->getValue($model,
                $this->normalizeKeyIfEnum($key)
            ));
    }

    /**
     * Sets the serialized value for the specified key in the given MetaFields model.
     *
     * @param  MetaFields  $model The MetaFields model in which to set the value.
     * @param  string|BackedEnum  $key The key for which to set the value. Can be either a string or a BackedEnum instance.
     * @param  mixed  $value The value to set. It can be of any type.
     * @return mixed The updated value.
     */
    public function setMetaFieldValue(MetaFields $model, string|BackedEnum $key, mixed $value): mixed
    {
        $key = $this->normalizeKeyIfEnum($key);

        $serialized = $this->serializer->serialize($value);

        return $this->setValue($model, $key, $serialized);
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
     * Sets the cache key prefix for the current instance.
     *
     * @param  string  $prefix The cache key prefix to be set.
     * @return self Returns the current instance of the class.
     */
    public function setCacheKeyPrefix(string $prefix = ''): self
    {
        $this->cacheKeyPrefix = $prefix;

        return $this;
    }

    /**
     * Retrieves the value associated with the given key from the specified MetaFields model.
     *
     * @param  MetaFields  $model The MetaFields model from which to retrieve the value.
     * @param  mixed  $key The key to retrieve the value for.
     * @return mixed|null The retrieved value, or null if the key is not found.
     */
    protected function getValue(MetaFields $model, $key): mixed
    {
        return $model->metaFields->first(function ($item, $key) use ($key) {
            return $item->key === $key;
        });
    }

    /**
     * Sets the value associated with the given key in the specified MetaFields model.
     *
     * @param  MetaFields  $model The MetaFields model in which to set the value.
     * @param  string  $key The key to set the value for.
     * @param  mixed  $value The value to be set.
     * @return Model The newly created MetaFields model.
     */
    protected function setValue(MetaFields $model, string $key, mixed $value): Model
    {
        return $model->metaFields()->create([
            'key' => $key,
            'value' => $value,
        ]);
    }

    /**
     * Normalizes the given key into a string.
     *
     * @param  string|BackedEnum  $key The key to normalize. Can be either a string or a BackedEnum instance.
     * @return string The normalized key as a string.
     *
     * @throws InvalidKeyException If the key is a BackedEnum instance and its value is not a string.
     */
    protected function normalizeKeyIfEnum(string|BackedEnum $key): string
    {
        if ($key instanceof BackedEnum) {
            $value = $key->value;
            if (! is_string($value)) {
                throw new InvalidKeyException('Expected $key to be of type string. Got '.gettype($value));
            }

            $key = $value;
        }

        return $key;
    }

    /**
     * Serializes the given value.
     *
     * @param  mixed  $value The value to be serialized.
     * @return string The serialized value.
     */
    protected function serialize(mixed $value): string
    {
        return $this->serializer->serialize($value);
    }

    /**
     * Unserializes the given value using the Serializer.
     *
     * @param  mixed  $value The value to unserialize.
     * @return mixed The unserialized value.
     */
    protected function unserialize(mixed $value): mixed
    {
        return $this->serializer->unserialize($value);
    }

    protected function getCacheKey($key): string
    {

    }
}
