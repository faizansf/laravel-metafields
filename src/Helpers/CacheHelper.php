<?php

namespace FaizanSf\LaravelMetafields\Helpers;

use BackedEnum;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use FaizanSf\LaravelMetafields\Facades\MetaKeyHelperFacade;
use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * Generates a cache key for a model's metafield storage, combining a configurable prefix, model's class name,
     * and primary key. The optional key parameter, when provided, specifies a particular metafield; otherwise,
     * it represents all metafields. Null values in model details are substituted with 'null'.
     *
     * @param  Metafieldable  $model The model for which the cache key is being generated.
     * @param  string|null  $key An optional key for a specific metafield. If null, the key represents all metafields.
     * @return string The constructed cache key.
     */
    public function getKey(Metafieldable $model, ?string $key = null): string
    {
        return collect([
            config('metafields.cache_key_prefix'),
            class_basename($model),
            $model->getKey() ?? 'null',
            $key,
        ])->filter(function ($value) {
            return $value !== null;
        })->join(':');
    }

    /**
     * Clears the cache for the given model and the given key.
     *
     * @param  Metafieldable  $model The model for which to clear the cache.
     * @param  string|null  $key The key for which to clear the cache.
     */
    public function clear(Metafieldable $model, string|BackedEnum|null $key = null): void
    {
        $key = MetaKeyHelperFacade::normalizeKey($key);

        Cache::forget($this->getKey($model, $key));
    }
}
