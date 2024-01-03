<?php

namespace FaizanSf\LaravelMetafields\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \FaizanSf\LaravelMetafields\LaravelMetafields
 */
class LaravelMetafields extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \FaizanSf\LaravelMetafields\LaravelMetafields::class;
    }
}
