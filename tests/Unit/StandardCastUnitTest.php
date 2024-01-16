<?php

use FaizanSf\LaravelMetafields\Models\Metafield;
use FaizanSf\LaravelMetafields\ValueSerializers\StandardValueSerializer;

beforeEach(function () {
    $this->cast = new StandardValueSerializer();
    $this->model = new Metafield();

});

it('unserializes data correctly', function () {
    $data = serialize(['key' => 'value']);
    $result = $this->cast->unserialize($this->model, 'value', $data, []);

    expect($result)->toBeArray()->and($result)->toHaveKey('key', 'value');
});

it('serializes data correctly', function () {
    $data = ['key' => 'value'];
    $result = $this->cast->serialize($this->model, 'value', $data, []);

    expect($result)->toEqual(serialize($data));
});

it('respects allowed classes for unserialization', function () {

    config()->set('metafields.unserialize_allowed_class', [Metafield::class]);

    $object = new Metafield();
    $serialized = serialize($object);
    $result = $this->cast->unserialize($this->model, 'value', $serialized, []);

    expect($result)->toBeInstanceOf(Metafield::class);
});

it('unserialize not allowed objects into __PHP_Incomplete_Class', function () {
    config('metafields.unserialize_allowed_class', []);

    $object = new Metafield();
    $serialized = serialize($object);

    expect($this->cast->unserialize($this->model, 'value', $serialized, []))
        ->toBeInstanceOf(__PHP_Incomplete_Class::class);
});
