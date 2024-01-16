<?php

use FaizanSf\LaravelMetafields\ValueSerializers\JsonValueSerializer;
use FaizanSf\LaravelMetafields\Tests\TestSupport\Models\Car;

beforeEach(function () {
    $this->cast = new JsonValueSerializer();
    $this->model = new Car();
});

it('decodes JSON correctly', function () {
    $json = '{"key":"value"}';
    $decoded = $this->cast->get($this->model, 'json_field', $json, []);

    expect($decoded)->toBeArray()->and($decoded)->toHaveKey('key', 'value');
});

it('encodes JSON correctly', function () {
    $array = ['key' => 'value'];
    $encoded = $this->cast->set($this->model, 'json_field', $array, []);

    expect($encoded)->toBeJson()->and($encoded)->toEqual(json_encode($array));
});

it('throws exception on invalid JSON when decoding', function () {
    $invalidJson = '{"key": "value"'; // Missing closing brace
    $this->cast->get($this->model, 'json_field', $invalidJson, []);
})->throws(JsonException::class);

it('throws exception on invalid value when encoding', function () {
    $invalidValue = "\xB1\x31"; // Invalid UTF-8 sequence
    $this->cast->set($this->model, 'json_field', $invalidValue, []);
})->throws(JsonException::class);
