<?php

use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Facades\CacheHandler;
use FaizanSf\LaravelMetafields\Facades\LaravelMetafields;

it('normalizes enum key', function ($testKey) {
    $key = $testKey;
    $normalizedKey = LaravelMetafields::normalizeKey($key);

    expect($normalizedKey)->toBeString()->toEqual($key->value);
})->with('testKeys');

it('throws exception where enum key is not a string', function ($invalidTestKey) {
    LaravelMetafields::normalizeKey($invalidTestKey);
})->throws(InvalidKeyException::class)->with('invalidTestKeys');

it('does not normalize string key', function ($stringKey) {
    $key = $stringKey;
    $normalizedKey = LaravelMetafields::normalizeKey($key);

    expect($normalizedKey)->toBeString()->toEqual($key);
})->with('stringKeys');

it('returns single field cache key in correct format', function ($key) {
    $key = LaravelMetafields::normalizeKey($key);
    $car = makeCarInstance();
    $prefix = 'LaravelMetafields';

    config()->set('metafields.cache_key_prefix', $prefix);

    $cacheKey = CacheHandler::getKey($car, $key);
    $expected = $prefix.':Car:'.$car->getKey().':'.$key;

    expect($cacheKey)->toBeString()->toEqual($expected);
})->with('stringKeys', 'testKeys');

it('returns all metafields cache key in correct format', function () {
    $prefix = 'LaravelMetafields';
    config()->set('metafields.cache_key_prefix', $prefix);
    $car = makeCarInstance();

    $cacheKey = CacheHandler::getKey($car);
    $expected = $prefix.':Car:'.$car->getKey();

    expect($cacheKey)->toBeString()->toEqual($expected);

});

it('caches the result when enabled, then clears the cache', function () {
    $car = makeCarInstance();
    $key = 'model';
    $cacheKey = CacheHandler::getKey($car, $key);

    LaravelMetafields::setModel($car)->runCachedOrDirect(function () {
        return 1999;
    }, $key);

    expect(cache()->has($cacheKey))->toBeTrue();

    CacheHandler::clear($car, $key);

    expect(cache()->has($cacheKey))->toBeFalse();

});

it('doesnt caches the result when cache is disabled', function () {
    config()->set('metafields.cache_enabled', false);

    $car = makeCarInstance();
    $key = 'model';
    $cacheKey = CacheHandler::getKey($car, $key);
    LaravelMetafields::setModel($car)->runCachedOrDirect(function () {
        return 1999;
    }, $key);

    expect(cache()->has($cacheKey))->toBeFalse();
});
