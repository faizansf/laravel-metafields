<?php

namespace FaizanSf\LaravelMetafields\Tests;

use FaizanSf\LaravelMetafields\LaravelMetafieldsServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function getEnvironmentSetUp($app): void
    {
        $migration = include __DIR__.'/../database/migrations/create_metafields_table.php.stub';
        $migration->up();
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelMetafieldsServiceProvider::class,
        ];
    }
}
