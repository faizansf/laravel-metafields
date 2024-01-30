<?php

namespace FaizanSf\LaravelMetafields\Support\Abstract;

use BackedEnum;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Facades\MetaKeyFacade;
use Illuminate\Support\Facades\Cache;

abstract class MetaCacheHelper
{
    public function __construct(protected NormalizeMetaKeyHelper $keyNormalizer)
    {}

    /**
     * Generates a cache key for a model's metafield storage, combining a configurable prefix, model's class name,
     * and primary key. The optional key parameter, when provided, specifies a particular metafield; otherwise,
     * it represents all metafields. Null values in model details are substituted with 'null'.
     *
     * @param Metafieldable $model The model for which the cache key is being generated.
     * @param string|BackedEnum|null $key An optional key for a specific metafield. If null, the key represents all metafields.
     * @return string The constructed cache key.
     * @throws InvalidKeyException
     */
    public function getKey(Metafieldable $model, string|BackedEnum|null $key = null): string
    {
        $key = $this->keyNormalizer->normalize($key);

        return collect([
            config('metafields.cache_key_prefix'),
            class_basename($model),
            $model->getKey() ?? 'null',
            $key,
        ])->filter()->join(':');
    }

    /**
     * Clears the cache for the given model and the given key.
     *
     * @param Metafieldable $model The model for which to clear the cache.
     * @param string|null $key The key for which to clear the cache.
     * @throws InvalidKeyException
     */
    public function clear(Metafieldable $model, string|BackedEnum|null $key = null): void
    {
        $key = $this->keyNormalizer->normalize($key);

        Cache::forget($this->getKey($model, $key));
    }
}
