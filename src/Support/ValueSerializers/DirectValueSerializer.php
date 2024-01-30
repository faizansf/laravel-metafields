<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Support\ValueSerializers;

use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;

final class DirectValueSerializer implements ValueSerializer
{
    public function unserialize($serialized): mixed
    {
        return $serialized;
    }

    public function serialize($value): mixed
    {
        return $value;
    }
}
