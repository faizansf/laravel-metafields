<?php

use FaizanSf\LaravelMetafields\Support\ValueSerializers\DirectValueSerializer;

beforeEach(function () {
    $this->serializer = new DirectValueSerializer();
});

it('returns same value on unserialize', function ($value) {
    $unserialized = $this->serializer->unserialize($value);

    expect($unserialized)->toBe($value);
})->with('data');

it('returns same value on serialize', function ($value) {
    $serialized = $this->serializer->serialize($value);

    expect($serialized)->toBe($value);
})->with('data');

dataset('data', [
    ['abc'],
    [[1, 2, 3]],
]);
