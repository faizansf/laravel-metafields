<?php
declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Proxies;

use BackedEnum;
use FaizanSf\LaravelMetafields\Exceptions\BadMethodCallException;
use FaizanSf\LaravelMetafields\LaravelMetafields;
use Illuminate\Support\Collection;

/**
 * Proxy class to handle metafield operations without caching.
 *
 * This class acts as a proxy to a LaravelMetafields singleton instance, temporarily overriding
 * its caching behavior.
 * It allows for operations on the LaravelMetafields instance
 * to be executed without utilizing the cache, then restores the original cache
 * settings after the operation is complete.
 * @method mixed get(string|BackedEnum $key, mixed $default = null)
 * @method Collection getAll(array $default = [])
 */
final class WithoutCacheLaravelMetafieldsProxy
{
    /**
     * @var array $allowedMethods Methods from LaravelMetafields class added to this array will be allowed to be executed through this proxy
     */
    private array $allowedMethods = [
        'get', 'getAll'
    ];

    /**
     * @param LaravelMetafields $parent The LaravelMetafields instance to proxy.
     */
    public function __construct(
        private readonly LaravelMetafields $parent)
    {}

    /**
     * Magic method to handle method calls to the proxied LaravelMetafields instance.
     *
     * It intercepts method calls, executes them on the LaravelMetafields instance without
     * caching, and then restores the original cache settings.
     *
     * @param  string  $name  The name of the method being called.
     * @param  array  $arguments  The arguments passed to the method.
     * @return mixed The result of the method call.
     */
    public function __call(string $name, array $arguments)
    {
        if (!method_exists($this->parent, $name) || !$this->isMethodAllowed($name)) {
            throw BadMethodCallException::withMessage($name, $this->allowedMethods);
        }
        //Temporary disabled cache
        $this->parent->setTemporaryDisableCache(true);

        $result = $this->parent->$name(...$arguments);

        //Restore the original cache settings
        $this->parent->setTemporaryDisableCache(false);

        return $result;
    }

    /**
     * Check whether a method is allowed to be called from Proxy
     * @param $methodName
     * @return bool
     */
    private function isMethodAllowed($methodName): bool
    {
        return in_array($methodName, $this->allowedMethods, true);
    }
}
