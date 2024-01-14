<?php

namespace FaizanSf\LaravelMetafields\Utils;

class CacheContext
{
    public function __construct(
        protected bool $cacheEnabled,
        protected ?int $ttl
    ) {
    }

    /**
     * Create a new CacheContext instance.
     *
     * @param  string  $class The class name to create the context for.
     */
    public static function make(string $class): CacheContext
    {
        return new self(
            $class::$cacheEnabled ?? config('metafields.cache_enabled', true),
            $class::$ttl ?? config('metafields.cache_ttl')
        );
    }

    /**
     * Check if caching is enabled.
     */
    public function isCacheEnabled(): bool
    {
        return $this->cacheEnabled;
    }

    /**
     * Get the Time to Live for cache.
     *
     * @return ?int
     */
    public function getTtl(): ?int
    {
        return $this->ttl;
    }
}
