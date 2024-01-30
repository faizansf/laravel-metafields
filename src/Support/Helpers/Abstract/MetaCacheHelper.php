<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Support\Helpers\Abstract;

use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use FaizanSf\LaravelMetafields\DataTransferObjects\NormalizedKey;
use Illuminate\Support\Facades\Cache;

abstract class MetaCacheHelper
{
    /**
     * Generates a cache key for a model's metafield storage, combining a configurable prefix, model's class name,
     * and primary key. The optional key parameter, when provided, specifies a particular metafield; otherwise,
     * it represents all metafields. Null values in model details are substituted with 'null'.
     *
     * @param  Metafieldable  $model  The model for which the cache key is being generated.
     * @param  NormalizedKey  $key  Metafield key to be used
     * @return string The constructed cache key.
     */
    public function getCacheKey(Metafieldable $model, NormalizedKey $key): string
    {
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
     * @param  Metafieldable  $model  The model for which to clear the cache.
     * @param  NormalizedKey  $key  The key for which to clear the cache.
     */
    public function clear(Metafieldable $model, NormalizedKey $key): void
    {
        Cache::forget($this->getCacheKey($model, $key));
    }

    /**
     * Checks if cache exists for the given model and the given key
     */
    public function isCacheExist(Metafieldable $model, NormalizedKey $key): bool
    {
        return Cache::has($this->getCacheKey($model, $key));
    }
}
