<?php

use FaizanSf\LaravelMetafields\Dependencies\Serializers\StandardSerializer;
use FaizanSf\LaravelMetafields\Models\MetaField;

it('can serialize string using standard serializer', function (mixed $testSubject) {
    $serializer = new StandardSerializer();

    $serializedString = $serializer->serialize($testSubject);

    expect($serializedString)->toEqual(serialize($testSubject));
})->with('testSubjects');

it('can unserialize string using standard serializer', function (mixed $testSubject) {
    $serializer = new StandardSerializer();

    $serializedString = serialize($testSubject);

    $unserializeString = $serializer->unserialize($serializedString);

    $type = gettype($testSubject);

    $expectation = match ($type) {
        'string' => 'toBeString',
        'array' => 'toBeArray',
        'boolean' => 'toBeBool',
        'integer', 'double' => 'toBeNumeric',
        default => null,
    };

    if ($expectation !== null) {
        expect($unserializeString)->$expectation();
    }

    expect($serializedString)->toEqual(serialize($testSubject))
        ->and($unserializeString)->toEqual($testSubject);
})->with('testSubjects');

it('will unserialize not allowed objects into __PHP_Incomplete_Class', function () {
    config('metafields.unserialize_allowed_class', []);

    $serializer = new StandardSerializer();

    $object = new MetaField();

    $serializedString = $serializer->serialize($object);

    expect($serializer->unserialize($serializedString))->toBeInstanceOf(__PHP_Incomplete_Class::class);

});

it('will unserialize allowed objects', function () {
    config('metafields.unserialize_allowed_class', [MetaField::class]);
    $serializer = new StandardSerializer();

    $object = new MetaField();

    $serializedString = $serializer->serialize($object);

    expect($serializer->unserialize($serializedString))->toBeInstanceOf(__PHP_Incomplete_Class::class);

});

dataset('testSubjects', [
    'Hello',
    '12345',
    'SpecialChars!@#',
    ' ',
    [1, 2, 3],
    ['apple', 'banana', 'orange'],
    ['key' => 'value', 'number' => 123],
    false,
    1,
    '10.4',
    45.5,
    null,
]);
