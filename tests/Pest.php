<?php

use FaizanSf\LaravelMetafields\Tests\TestCase;
use Workbench\App\Enums\CarMetafieldsEnum;
use Workbench\App\Enums\CarMetafieldsNonStringEnum;
use Workbench\App\Models\Person;
use Workbench\App\Models\PersonDirectValue;

uses(TestCase::class)->in(__DIR__);

function makePersonInstance()
{
    $person = Person::factory()->make();
    $person->id = 1;

    return $person;
}

function makePersonDirectValueInstance()
{
    $personDirectValue = PersonDirectValue::factory()->make();
    $personDirectValue->id = 1;

    return $personDirectValue;
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
