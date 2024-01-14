<?php

use FaizanSf\LaravelMetafields\Tests\TestCase;
use FaizanSf\LaravelMetafields\Tests\TestSupport\Enums\CarMetafieldsEnum;
use FaizanSf\LaravelMetafields\Tests\TestSupport\Enums\CarMetafieldsNonStringEnum;
use FaizanSf\LaravelMetafields\Tests\TestSupport\Models\Car;

uses(TestCase::class)->in(__DIR__);


function makeCarInstance(){
    $car = Car::newFactory()->make();
    $car->id = 1;

    return $car;
}

dataset('testKeys', [
    CarMetafieldsEnum::MODEL,
    CarMetafieldsEnum::COLOR,
]);

dataset('invalidTestKeys', [
    CarMetafieldsNonStringEnum::MODEL,
    CarMetafieldsNonStringEnum::COLOR,
]);

dataset('stringKeys', [
    'MODEL',
    'COLOR',
]);
