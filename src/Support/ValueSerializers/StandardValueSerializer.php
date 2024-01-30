<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\ValueSerializers;

use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use Illuminate\Support\Arr;

class StandardValueSerializer implements ValueSerializer
{
    public function unserialize($serialized): mixed
    {
        $allowedClasses = Arr::wrap(config(
            'metafields.unserialize_allowed_class', []
        ));

        return unserialize($serialized, [
            'allowed_classes' => $allowedClasses,
        ]);
    }

    public function serialize($value): string
    {
        return serialize($value);
    }
}
