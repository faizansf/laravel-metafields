<?php

use FaizanSf\LaravelMetafields\Exceptions\DuplicateKeyException;
use FaizanSf\LaravelMetafields\Support\Helpers\NormalizeMetaKeyHelper;
use FaizanSf\LaravelMetafields\Support\ValueSerializers\DirectValueSerializer;
use FaizanSf\LaravelMetafields\Support\ValueSerializers\JsonValueSerializer;
use FaizanSf\LaravelMetafields\Support\ValueSerializers\StandardValueSerializer;

beforeEach(function(){
    $this->model = makePersonInstance();
    $this->keyNormalizer = new NormalizeMetaKeyHelper;
});


it('can set and get a metafield value', function () {
    $this->model->setMetafield('foo', 'bar');

    $metafield = $this->model->getMetafield('foo');

    expect($metafield)->toBe('bar');
});

it('can get all metafields', function () {
    $this->model->setMetafield('foo', 'bar');
    $this->model->setMetafield('baz', 'qux');

    $metafields = $this->model->getAllMetafields();

    expect($metafields->count())->toBe(2)
        ->and($metafields->has('foo'))->toBeTrue()
        ->and($metafields->has('baz'))->toBeTrue();
});

it('can delete a metafield', function () {
    $this->model->setMetafield('foo', 'bar');

    $this->model->deleteMetafield('foo');

    expect($this->model->getMetafield('foo'))->toBeNull();
});

it('can delete all the metafields', function () {
    $this->model->setMetafield('foo', 'bar');
    $this->model->setMetafield('baz', 'qux');

    $deletedMetafieldsCount = $this->model->deleteAllMetafields();

    expect($deletedMetafieldsCount)->toBe(2);
});

it('can register serializers for metafields', function () {
    $modelWithRegisteredMetafields = makePersonDirectValueInstance();

    $key1 = 'test-key'; //registered in Model
    $normalizedKey1 = $this->keyNormalizer->normalize($key1);
    $serializer1 = $modelWithRegisteredMetafields->getValueSerializer($normalizedKey1);

    $key2 = 'test-key-2'; //not registered in Model
    $normalizedKey2 = $this->keyNormalizer->normalize($key2);
    $serializer2 = $modelWithRegisteredMetafields->getValueSerializer($normalizedKey2);

    expect($serializer1)->toBe(DirectValueSerializer::class)
        //Since no serializer is registered for test-key-2, it should return null
        ->and($serializer2)->toBeNull();
});

it('throws an exception when trying to map a duplicate key to a different serializer', function(){
    $this->model->mapSerializer('test-key', StandardValueSerializer::class);
    $this->model->mapSerializer('test-key', JsonValueSerializer::class);
})->throws(DuplicateKeyException::class);
