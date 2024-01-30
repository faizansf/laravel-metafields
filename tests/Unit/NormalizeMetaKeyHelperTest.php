<?php

use FaizanSf\LaravelMetafields\DataTransferObjects\NormalizedKey;
use FaizanSf\LaravelMetafields\Exceptions\InvalidConfigurationException;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Support\Helpers\NormalizeMetaKeyHelper;
use Workbench\App\Enums\NonStringBackedEnum;
use Workbench\App\Enums\PersonMetafieldEnum;

beforeEach(function () {
    $this->keyNormalizer = new NormalizeMetaKeyHelper;
});

it('normalizes a string key', function () {
    $normalizedKey = $this->keyNormalizer->normalize('foo');

    expect($normalizedKey)
        ->toBeInstanceOf(NormalizedKey::class)
        ->and($normalizedKey->asString())->toBe('foo');
});

it('normalizes an enum key', function () {
    $normalizedKey = $this->keyNormalizer->normalize(PersonMetafieldEnum::EMAIL);

    expect($normalizedKey)
        ->toBeInstanceOf(NormalizedKey::class)
        ->and($normalizedKey->asString())->toBe(PersonMetafieldEnum::EMAIL->value);
});

it('throws an exception for invalid key', function () {
    $normalizedKey = $this->keyNormalizer->normalize(NonStringBackedEnum::ONE);
})->throws(InvalidKeyException::class);

it('throws an exception for invalid not_allowed_keys value', function () {
    config()->set('metafields.not_allowed_keys', 'invalid');
    $this->keyNormalizer->normalize('foo');
})->throws(InvalidConfigurationException::class);

it('returns throws an exception when a not allowed key is used', function () {
    config()->set('metafields.not_allowed_keys', ['foo']);
    $this->keyNormalizer->normalize('foo');
})->throws(InvalidKeyException::class);

it('normalizes keys of array', function () {
    $keys = ['foo', 'bar', 'baz'];
    $normalizedKeys = $this->keyNormalizer->normalizeKeys($keys);

    expect($normalizedKeys)->toBeArray()
        ->and($normalizedKeys[0])->toBeInstanceOf(NormalizedKey::class)
        ->and($normalizedKeys[1])->toBeInstanceOf(NormalizedKey::class)
        ->and($normalizedKeys[2])->toBeInstanceOf(NormalizedKey::class);
});
