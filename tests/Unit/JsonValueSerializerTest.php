<?php

use FaizanSf\LaravelMetafields\Support\ValueSerializers\JsonValueSerializer;

beforeEach(function () {
    $this->serializer = new JsonValueSerializer();
});

it('decodes JSON correctly', function () {
    $json = '{"key":"value"}';
    $decoded = $this->serializer->unserialize($json);

    expect($decoded)->toBeArray()->and($decoded)->toHaveKey('key', 'value');
});

it('encodes JSON correctly', function () {
    $array = ['key' => 'value'];
    $encoded = $this->serializer->serialize($array);

    expect($encoded)->toBeJson()->and($encoded)->toEqual(json_encode($array, JSON_THROW_ON_ERROR));
});

it('throws exception on invalid JSON when decoding', function () {
    $invalidJson = '{"key": "value"'; // Missing closing brace
    $this->serializer->unserialize($invalidJson);
})->throws(JsonException::class);

it('throws exception on invalid value when encoding', function () {
    $invalidValue = "\xB1\x31"; // Invalid UTF-8 sequence
    $this->serializer->serialize($invalidValue);
})->throws(JsonException::class);
