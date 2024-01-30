<?php

use FaizanSf\LaravelMetafields\Contracts\DataTransferObject;
use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use Illuminate\Database\Eloquent\Model;

it('should use strict types')
    ->expect('FaizanSf\LaravelMetafields')
    ->toUseStrictTypes();
it('will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

it('has interfaces in src/Contracts')
    ->expect('FaizanSf\LaravelMetafields\Contracts')
    ->toBeInterfaces();

it('has traits in src/Concerns')
    ->expect('FaizanSf\LaravelMetafields\Concerns')
    ->toBeTraits();

it('has Exceptions in src/Exceptions')
    ->expect('FaizanSf\LaravelMetafields\Exceptions')
    ->toExtend(Exception::class);

it('has models in src/Models')
    ->expect('FaizanSf\LaravelMetafields\Models')
    ->toExtend(Model::class);

it('has dtos in src/DataTransferObjects')
    ->expect('FaizanSf\LaravelMetafields\DataTransferObjects')
    ->toImplement(DataTransferObject::class)
    ->toBeFinal();

it('has helpers in src/Support/Helpers')
    ->expect('FaizanSf\LaravelMetafields\Support\Helpers')
    ->toHaveSuffix('Helper');

it('has serializers in src/Support/ValueSerializers')
    ->expect('FaizanSf\LaravelMetafields\Support\ValueSerializers')
    ->toHaveSuffix('ValueSerializer')
    ->toImplement(ValueSerializer::class)
    ->toBeFinal();
