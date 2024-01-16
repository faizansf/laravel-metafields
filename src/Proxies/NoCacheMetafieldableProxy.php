<?php

namespace FaizanSf\LaravelMetafields\Proxies;

use FaizanSf\LaravelMetafields\Contracts\Metafieldable;

/**
 * Proxy class to handle metafield operations without caching.
 *
 * This class acts as a proxy to a Metafieldable instance, temporarily overriding
 * its caching behavior. It allows for operations on the Metafieldable instance
 * to be executed without utilizing the cache, then restores the original cache
 * settings after the operation is complete.
 */
class NoCacheMetafieldableProxy
{

    /**
     * Creates a new NoCacheMetafieldableProxy instance.
     *
     * @param Metafieldable $parent The Metafieldable instance to proxy.
     * @param bool $originalCacheSetting The original cache setting of the Metafieldable instance.
     */
    public function __construct(
        private readonly Metafieldable $parent,
        private readonly bool $originalCacheSetting)
    {}

    /**
     * Magic method to handle method calls to the proxied Metafieldable instance.
     *
     * It intercepts method calls, executes them on the Metafieldable instance without
     * caching, and then restores the original cache settings.
     *
     * @param string $name The name of the method being called.
     * @param array $arguments The arguments passed to the method.
     * @return mixed The result of the method call.
     */
    public function __call(string $name, ...$arguments) {
        $result = $this->parent->$name(...$arguments);
        $this->parent->setMetafieldCacheEnabled($this->originalCacheSetting);
        return $result;
    }
}
