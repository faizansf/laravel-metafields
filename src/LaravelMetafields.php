<?php

namespace FaizanSf\LaravelMetafields;

use FaizanSf\LaravelMetafields\Contracts\KeyGenerator;
use FaizanSf\LaravelMetafields\Contracts\Serializer;
use FaizanSf\LaravelMetafields\Contracts\MetaField;

class LaravelMetafields
{
    protected static bool $cacheEnabled =  true;
    protected string $cacheKeyPrefix = '';

    public function __construct(
        protected KeyGenerator $keyGenerator,
        protected Serializer $valueSerializer,
    ) {
    }

    public function get()
    {

    }

    public function set()
    {

    }

    public function enableCache()
    {

    }

    public function disableCache()
    {

    }

    public function setCacheKeyPrefix()
    {

    }

    protected function getCacheKey($key){

    }


    protected function prepareValue()
    {

    }

    protected function prepareKey()
    {

    }

    protected function serialize()
    {

    }

    protected function unserialze()
    {

    }
}
