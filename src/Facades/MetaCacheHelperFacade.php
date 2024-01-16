<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Facades;

use Illuminate\Support\Facades\Facade;


/**
 * @see \FaizanSf\LaravelMetafields\Helpers\CacheHelper
 */
class MetaCacheHelperFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \FaizanSf\LaravelMetafields\Helpers\CacheHelper::class;
    }
}
