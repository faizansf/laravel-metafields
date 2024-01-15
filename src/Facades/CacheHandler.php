<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \FaizanSf\LaravelMetafields\CacheHandler
 */
class CacheHandler extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \FaizanSf\LaravelMetafields\Utils\CacheHandler::class;
    }
}
