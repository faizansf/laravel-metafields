<?php

namespace FaizanSf\LaravelMetafields\Helpers;

use BackedEnum;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use Illuminate\Support\Arr;

class MetaKeyHelper
{
    /**
     * Normalizes the given key into a string.
     *
     * @param  string|BackedEnum  $key  The key to normalize. Can be either a string or a BackedEnum instance.
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

    /**
     * Normalizes the given keys into a string and returns the normalized key array
     *
     * @param  array<int, string|BackedEnum>  $keys
     * @return array<int, string> The normalized
     */
    public function normalizeKeys(array $keys): array
    {
        return Arr::map($keys, function ($key) {
            return $this->normalizeKey($key);
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
        return is_string($key);
    }
}
