<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields;

use FaizanSf\LaravelMetafields\Commands\LaravelMetafieldsCommand;
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

    public function register(): void
    {
        $this->app->singleton(LaravelMetafields::class, function () {
            return (new LaravelMetafields)
                ->setCacheStatus(config('metafields.cache_enabled'))
                ->setCacheTtl(config('metafields.cache_ttl'))
                ->setCacheKeyPrefix(config('metafields.cache_key_prefix'));
        });

    }
}
