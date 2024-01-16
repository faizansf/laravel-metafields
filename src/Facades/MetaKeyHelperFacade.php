<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Facades;

use FaizanSf\LaravelMetafields\Helpers\MetaKeyHelper;
use Illuminate\Support\Facades\Facade;

/**
 * @see \FaizanSf\LaravelMetafields\Helpers\MetaKeyHelper
 */
class MetaKeyHelperFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return MetaKeyHelper::class;
    }
}
