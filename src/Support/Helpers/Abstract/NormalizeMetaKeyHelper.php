<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Support\Helpers\Abstract;

use BackedEnum;
use FaizanSf\LaravelMetafields\DataTransferObjects\NormalizedKey;
use FaizanSf\LaravelMetafields\Exceptions\InvalidConfigurationException;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use Illuminate\Support\Arr;

abstract class NormalizeMetaKeyHelper
{
    /**
     * Normalizes the given key into a string.
     *
     * @param  string|BackedEnum  $key  The key to normalize. Can be either a string or a BackedEnum instance.
     * @return NormalizedKey The normalized key as a string.
     *
     * @throws InvalidKeyException
     */
    public function normalize(string|BackedEnum $key, bool $ignoreKeyValidation = false): NormalizedKey
    {
        $keyValue = $key instanceof BackedEnum ? $key->value : $key;

        if (! $ignoreKeyValidation && ! $this->isValidKey($keyValue)) {
            throw InvalidKeyException::withMessage(key: $keyValue);
        }

        return new NormalizedKey($keyValue);
    }

    /**
     * Normalizes the given keys into a string and returns the normalized key array
     *
     * @param  array<int, string|BackedEnum>  $keys
     * @return array<int, string> The normalized
     */
    public function normalizeKeys(array $keys): array
    {
        return Arr::map($keys, function ($key) {
            return $this->normalize($key);
        });
    }

    /**
     * Checks if the given key is a valid key for a metafield.
     *
     * @param  mixed  $key  The key to check.
     * @return bool True if the key is valid, false otherwise.
     */
    private function isValidKey(mixed $key): bool
    {
        return is_string($key) &&
            ! in_array($key, $this->getNotAllowedKeys(), true) &&
            $key !== config('metafields.all_metafields_cache_key');
    }

    /**
     * Returns the keys that are not allowed for a metafield.
     *
     * @return array<int, string>
     */
    private function getNotAllowedKeys(): array
    {
        $notAllowedConfig = config('metafields.not_allowed_keys', []);

        if (! is_array($notAllowedConfig)) {
            throw InvalidConfigurationException::withMessage("'not_allowed_keys' should be an array");
        }

        return $notAllowedConfig;
    }
}
