<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields;

use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\MetaCacheHelper as BaseCacheHelper;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\NormalizeMetaKeyHelper as BaseNormalizeMetaKeyHelper;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\SerializeValueHelper as BaseSerializeValueHelper;
use FaizanSf\LaravelMetafields\Support\ValueSerializers\StandardValueSerializer;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelMetafieldsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-metafields')
            ->hasConfigFile()
            ->hasMigration('create_metafields_table');
    }

    public function packageBooted(): void
    {
        $this->app->singleton(BaseCacheHelper::class, Support\Helpers\MetaCacheHelper::class);
        $this->app->singleton(BaseNormalizeMetaKeyHelper::class, Support\Helpers\NormalizeMetaKeyHelper::class);
        $this->app->singleton(BaseSerializeValueHelper::class, Support\Helpers\SerializeValueHelper::class);

        $this->app->singleton(ValueSerializer::class, function ($app) {
            return $app->make(BaseSerializeValueHelper::class)
                ->make(config('metafields.default_serializer', StandardValueSerializer::class));
        });

    }
}
