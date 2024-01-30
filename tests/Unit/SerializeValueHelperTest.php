<?php

use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use FaizanSf\LaravelMetafields\Exceptions\InvalidValueSerializerException;
use FaizanSf\LaravelMetafields\Models\Metafield;
use FaizanSf\LaravelMetafields\Support\Helpers\NormalizeMetaKeyHelper;
use FaizanSf\LaravelMetafields\Support\Helpers\SerializeValueHelper;
use FaizanSf\LaravelMetafields\Support\ValueSerializers\JsonValueSerializer;
use FaizanSf\LaravelMetafields\Support\ValueSerializers\StandardValueSerializer;
use Workbench\App\ValueSerializers\InvalidValueSerializer;

beforeEach(function () {
    $this->serializer = new SerializeValueHelper;
    $this->model = makePersonInstance();
    $this->keyNormalizer = new NormalizeMetaKeyHelper;
});

it('validates the provided serializer', function () {
    $serializer1 = ValueSerializer::class;
    $serializer2 = StandardValueSerializer::class;
    $serializer3 = Metafield::class;

    expect($this->serializer->isValidSerializer($serializer1))->toBe(true)
        ->and($this->serializer->isValidSerializer($serializer2))->toBe(true)
        ->and($this->serializer->isValidSerializer($serializer3))->toBe(false);
});

it('resolves the serializer for the provided field', function () {
    $this->model->mapSerializer('key', StandardValueSerializer::class);
    $this->model->mapSerializer('key-2', JsonValueSerializer::class);

    config()->set('metafields.default_serializer', StandardValueSerializer::class);

    expect($this->serializer->resolve($this->model, $this->keyNormalizer->normalize('key')))
        ->toBeInstanceOf(StandardValueSerializer::class)

        ->and($this->serializer->resolve($this->model, $this->keyNormalizer->normalize('key-2')))
        ->toBeInstanceOf(JsonValueSerializer::class)
        //Since no Serializer is defined for this key, the default serializer will be returned
        ->and($this->serializer->resolve($this->model, $this->keyNormalizer->normalize('key-3')))
        ->toBeInstanceOf(StandardValueSerializer::class);
});

it('validates and make the serializer instances', function () {
    $serializer = $this->serializer->make(JsonValueSerializer::class);

    expect($serializer)->toBeInstanceOf(ValueSerializer::class);
});

it('invalidates and throw exception for invalid serializer', function () {
    $this->serializer->make(InvalidValueSerializer::class);
})->throws(InvalidValueSerializerException::class);
