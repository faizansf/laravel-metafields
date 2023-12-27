<?php

namespace FaizanSf\LaravelMetafields;

use FaizanSf\LaravelMetafields\Commands\LaravelMetafieldsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelMetafieldsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-metafields')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-metafields_table')
            ->hasCommand(LaravelMetafieldsCommand::class);
    }
}
