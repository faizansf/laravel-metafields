<?php

namespace FaizanSf\LaravelMetafields\Support\Abstract;

use BackedEnum;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use Illuminate\Support\Arr;

abstract class NormalizeMetaKeyHelper
{

    private array $notAllowedKeys = [];

    public function __construct(){
        $notAllowedConfig = config('metafields.not_allowed_keys', []);

        if (is_array($notAllowedConfig)) {
            $this->notAllowedKeys = $notAllowedConfig;
        }
    }

    /**
     * Normalizes the given key into a string.
     *
     * @param string|BackedEnum $key The key to normalize. Can be either a string or a BackedEnum instance.
     * @return string The normalized key as a string.
     * @throws InvalidKeyException
     */
    public function normalize(string|BackedEnum $key): string
    {
        $keyValue = $key instanceof BackedEnum ? $key->value : $key;

        if (!$this->isValidKey($keyValue)) {
            throw InvalidKeyException::withMessage(key: $keyValue);
        }

        return $keyValue;
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
    protected function isValidKey(mixed $key): bool
    {
        return
            is_string($key) &&
            !in_array($key, $this->notAllowedKeys, true) &&
            $key !== config('metafields.all_metafields_cache_key');
    }
}
