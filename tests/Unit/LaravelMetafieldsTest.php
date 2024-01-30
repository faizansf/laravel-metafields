<?php

use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use FaizanSf\LaravelMetafields\DataTransferObjects\NormalizedKey;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Exceptions\InvalidValueSerializerException;
use FaizanSf\LaravelMetafields\Exceptions\ModelNotSetException;
use FaizanSf\LaravelMetafields\LaravelMetafields;
use FaizanSf\LaravelMetafields\Models\Metafield;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\MetaCacheHelper;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\NormalizeMetaKeyHelper;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\SerializeValueHelper;
use FaizanSf\LaravelMetafields\Support\ValueSerializers\JsonValueSerializer;
use FaizanSf\LaravelMetafields\Support\ValueSerializers\StandardValueSerializer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Workbench\App\Enums\NonStringBackedEnum;
use Workbench\App\Enums\PersonMetafieldEnum;
use Workbench\App\ValueSerializers\InvalidValueSerializer;

beforeEach(function () {
    $this->keyNormalizer = app(NormalizeMetaKeyHelper::class);
    $this->serializeValueHelper = app(SerializeValueHelper::class);
    $this->cacheHelper = app(MetaCacheHelper::class);
    $this->valueSerializer = app(ValueSerializer::class);

    $this->laravelMetafields = new LaravelMetafields(
        $this->keyNormalizer,
        $this->serializeValueHelper,
        $this->cacheHelper
    );

    $this->model = makePersonInstance();
    $this->laravelMetafields->setModel($this->model);
    $this->allMetafieldsCacheKey = $this->keyNormalizer->normalize(config('metafields.all_metafields_cache_key'), true);

});

it('throws an exception when the model is not set', function () {
    $this->laravelMetafields->unsetModel();
    $this->laravelMetafields->set('foo', 'bar');
})->throws(ModelNotSetException::class);

it('normalizes an enum key', function () {
    $key = PersonMetafieldEnum::EMAIL;

    $this->laravelMetafields->set($key, 'bar');

    $metafields = $this->laravelMetafields->getAll();

    expect($metafields->has($key->value))->toBeTrue();
});

it('throws an exception with invalid key', function ($key) {
    $this->laravelMetafields->set($key, 'bar');
})->with([NonStringBackedEnum::ONE, 'all-metafields'])->throws(InvalidKeyException::class);

it('throws an exception when invalid serializer is mapped with a key', function () {
    $this->model->mapSerializer('foo', InvalidValueSerializer::class);

    $this->laravelMetafields->set('foo', 'bar');
})->throws(InvalidValueSerializerException::class);

it('updates or creates a metafield successfully', function () {
    $updatedValue = 'updatedValue';
    $this->laravelMetafields->set('foo', 'bar');
    $totalMetafields = $this->model->metafields->count();

    $this->laravelMetafields->set('foo', $updatedValue);

    expect($this->model->metafields->count())->toBe($totalMetafields)
        ->and($this->laravelMetafields->get('foo'))->toBe($updatedValue);

});

it('serializes the value before setting a metafield', function () {
    $serializers = new StandardValueSerializer();

    $serializedString = $serializers->serialize('bar');

    $this->laravelMetafields->set('foo', 'bar');

    $metafield = Metafield::where('key', 'foo')->first();

    expect($metafield->value)->toBe($serializedString);
});

it('returns the value of a metafield after setting', function () {
    $value = $this->laravelMetafields->set('foo', 'bar');

    expect($value)->toBe('bar');
});

it('caches value when cache is enabled', function () {
    config()->set('metafields.cache_metafields', true);

    $this->laravelMetafields->set('foo', 'bar');

    $this->laravelMetafields->get('foo', 'bar');

    $normalizedKey = $this->keyNormalizer->normalize('foo');

    $cacheCheck = $this->cacheHelper->isCacheExist($this->model, $normalizedKey);

    expect($cacheCheck)->toBeTrue();

});

it('doesnt cache value when cache is disabled', function () {
    config()->set('metafields.cache_metafields', false);

    $this->laravelMetafields->set('foo', 'bar');

    $this->laravelMetafields->get('foo', 'bar');

    $normalizedKey = $this->keyNormalizer->normalize('foo');

    $cacheCheck = $this->cacheHelper->isCacheExist($this->model, $normalizedKey);

    expect($cacheCheck)->toBeFalse();
});

it('clears the cache after a setting is updated', function () {
    $this->laravelMetafields->set('foo', 'bar');

    $this->laravelMetafields->get('foo', 'bar');

    $normalizedKey = $this->keyNormalizer->normalize('foo');

    $cacheCheckBefore = $this->cacheHelper->isCacheExist($this->model, $normalizedKey);

    $this->laravelMetafields->set('foo', 'bar');

    $cacheCheckAfter = $this->cacheHelper->isCacheExist($this->model, $normalizedKey);

    expect($cacheCheckBefore)->toBeTrue()
        ->and($cacheCheckAfter)->toBeFalse();
});

it('returns a default value when no value is returned', function () {
    $unserializedValue = $this->laravelMetafields->get('baz', 'bar');

    expect($unserializedValue)->toBe('bar');

});

it('returns unserialized value', function ($value) {
    $this->laravelMetafields->set('foo', $value);

    $unserializedValue = $this->laravelMetafields->get('foo');

    expect($unserializedValue)->toBe($value);
})->with([
    ['bar' => 1, 'baz' => 'qux'],
    'some-value',
]);

it('returns unserialized value when getting all metafields', function () {
    $this->laravelMetafields->set('foo', 'bar');
    $this->laravelMetafields->set('quux', ['baz' => 'qu']);

    $metafields = $this->laravelMetafields->getAll();

    expect($metafields)->toBeInstanceOf(Collection::class)
        ->and($metafields->has('foo'))->toBeTrue()
        ->and($metafields->has('quux'))->toBeTrue()
        ->and($metafields->get('foo'))->toBe('bar')
        ->and($metafields->get('quux'))->toBe(['baz' => 'qu']);
});

it('returns false when trying to delete a non-existent metafield', function () {
    $delete = $this->laravelMetafields->delete('foo');

    expect($delete)->toBeFalse();
});

it('deletes a metafield', function () {
    $this->laravelMetafields->set('foo', 'bar');

    $this->laravelMetafields->delete('foo');

    $metafield = $this->laravelMetafields->get('foo');

    expect($metafield)->toBeNull();
});

it('deletes all metafields', function () {
    $this->laravelMetafields->set('foo', 'bar');

    $this->laravelMetafields->deleteAll();

    $metafields = $this->laravelMetafields->getAll();

    expect($metafields->isEmpty())->toBeTrue();
});

it('clears key cache and all metafield collection cache after delete', function () {
    $this->laravelMetafields->set('foo', 'bar');

    //Call the get method to create cache
    $this->laravelMetafields->get('foo');
    $this->laravelMetafields->getAll();

    $normalizedKey = $this->keyNormalizer->normalize('foo');

    $cacheCheckBefore = $this->cacheHelper->isCacheExist($this->model, $normalizedKey);
    $cacheCheckAllMetafieldsBefore = $this->cacheHelper->isCacheExist($this->model, $this->allMetafieldsCacheKey);

    $delete = $this->laravelMetafields->delete('foo');

    $cacheCheckAfter = $this->cacheHelper->isCacheExist($this->model, $normalizedKey);
    $cacheCheckAllMetafieldsAfter = $this->cacheHelper->isCacheExist($this->model, $this->allMetafieldsCacheKey);

    expect($delete)->toBeTrue()
        ->and($cacheCheckBefore)->toBeTrue()
        ->and($cacheCheckAllMetafieldsBefore)->toBeTrue()
        ->and($cacheCheckAfter)->toBeFalse()
        ->and($cacheCheckAllMetafieldsAfter)->toBeFalse();

});

it('normalizers key and validate serializer', function () {
    [$key, $serializer] = $this->laravelMetafields->getNormalizedKeyWithValidSerializer('some-key', StandardValueSerializer::class);

    expect($key)->toBeInstanceOf(NormalizedKey::class)
        ->and($serializer)->toBe(StandardValueSerializer::class);
});

it('temporarily disables cache for the current call', function () {
    Cache::shouldReceive('remember')->never();
    Cache::shouldReceive('forget');

    $this->laravelMetafields->set('foo', 'bar');

    $result = $this->laravelMetafields->withOutCache()->get('foo');

    expect($result)->toBe('bar');

});

it('resolves a serializer for a given key correctly', function () {
    $this->model->mapSerializer('foo', JsonValueSerializer::class);

    $data = ['bar' => 'baz'];
    $json = json_encode($data, JSON_THROW_ON_ERROR);

    $this->laravelMetafields->set('foo', $data);

    $metafield = Metafield::where('key', 'foo')->first();

    // Since we cannot test the resolveSerializer private method directly, we
    // set a custom serializer for a field and expect the class to resolve
    // the correct serializer and serialize to json as intended
    expect($metafield->value)->toBe($json);
});
