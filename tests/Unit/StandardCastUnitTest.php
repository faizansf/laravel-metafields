<?php

use FaizanSf\LaravelMetafields\Casts\StandardCast;
use FaizanSf\LaravelMetafields\Models\Metafield;

beforeEach(function () {
    $this->cast = new StandardCast();
    $this->model = new Metafield();

});

it('unserializes data correctly', function () {
    $data = serialize(['key' => 'value']);
    $result = $this->cast->get($this->model, 'value', $data, []);

    expect($result)->toBeArray()->and($result)->toHaveKey('key', 'value');
});

it('serializes data correctly', function () {
    $data = ['key' => 'value'];
    $result = $this->cast->set($this->model, 'value', $data, []);

    expect($result)->toEqual(serialize($data));
});

it('respects allowed classes for unserialization', function () {

    config()->set('metafields.unserialize_allowed_class', [Metafield::class]);

    $object = new Metafield();
    $serialized = serialize($object);
    $result = $this->cast->get($this->model, 'value', $serialized, []);

    expect($result)->toBeInstanceOf(Metafield::class);
});

it('unserialize not allowed objects into __PHP_Incomplete_Class', function () {
    config('metafields.unserialize_allowed_class', []);

    $object = new Metafield();
    $serialized = serialize($object);

    expect($this->cast->get($this->model, 'value', $serialized, []))
        ->toBeInstanceOf(__PHP_Incomplete_Class::class);
});
