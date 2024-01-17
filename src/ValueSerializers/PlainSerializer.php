<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\ValueSerializers;

use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;

class PlainSerializer implements ValueSerializer
{
    public function unserialize($serialized): mixed
    {
        return $serialized;
    }

    public function serialize($value): string
    {
        return $value;
    }
}
