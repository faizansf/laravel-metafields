<?php

use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use FaizanSf\LaravelMetafields\LaravelMetafields;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\MetaCacheHelper;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\NormalizeMetaKeyHelper;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\SerializeValueHelper;

/**
 * We will be testing WithoutCacheLaravelMetafieldsProxy indirectly through LaravelMetafields class
 * Because this class is not meant to be used directly
 */

beforeEach(function() {
    $this->keyNormalizer = app(NormalizeMetaKeyHelper::class);
    $this->serializeValueHelper = app(SerializeValueHelper::class);
    $this->cacheHelper = app(MetaCacheHelper::class);
    $this->valueSerializer = app(ValueSerializer::class);

    $this->laravelMetafields = new  LaravelMetafields(
        $this->keyNormalizer,
        $this->serializeValueHelper,
        $this->cacheHelper
    );

    $this->laravelMetafields->setModel(makePersonInstance());
});

it('throws an exception when not allowed methods are called', function() {
    $this->laravelMetafields->withOutCache()->set('foo', 'bar');
})->throws(\FaizanSf\LaravelMetafields\Exceptions\BadMethodCallException::class);

it('calls allowed method on LaravelMetafields class(parent)', function() {
    $this->laravelMetafields->set('foo', 'bar');

    $result = $this->laravelMetafields->withOutCache()->get('foo');

    expect($result)->toBe('bar');
});
