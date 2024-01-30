<?php

use FaizanSf\LaravelMetafields\Models\Metafield;
use FaizanSf\LaravelMetafields\Support\ValueSerializers\StandardValueSerializer;
use Workbench\App\Models\Person;

beforeEach(function () {
    $this->serializer = new StandardValueSerializer();
});

it('unserializes data correctly', function () {
    $data = serialize(['key' => 'value']);
    $result = $this->serializer->unserialize($data);

    expect($result)->toBeArray()->and($result)->toHaveKey('key', 'value');
});

it('serializes data correctly', function () {
    $data = ['key' => 'value'];
    $result = $this->serializer->serialize($data);

    expect($result)->toEqual(serialize($data));
});

it('respects allowed classes for unserialization', function () {

    config()->set('metafields.unserialize_allowed_class', [Person::class]);

    $object = makePersonInstance();
    $serialized = serialize($object);
    $result = $this->serializer->unserialize($serialized);

    expect($result)->toBeInstanceOf(Person::class);
});

it('unserialize not allowed objects into __PHP_Incomplete_Class', function () {
    config('metafields.unserialize_allowed_class', []);

    $object = makePersonInstance();
    $serialized = serialize($object);
    $result = $this->serializer->unserialize($serialized);

    expect($result)->toBeInstanceOf(__PHP_Incomplete_Class::class);
});
