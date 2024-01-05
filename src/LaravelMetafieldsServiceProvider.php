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
}
