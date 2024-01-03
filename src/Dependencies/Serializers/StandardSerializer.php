<?php

namespace FaizanSf\LaravelMetafields\Dependencies\Serializers;

use FaizanSf\LaravelMetafields\Contracts\Serializer as SerializerContract;
use Illuminate\Support\Arr;

class StandardSerializer implements SerializerContract
{
    public function serialize($value): string
    {
        return serialize($value);
    }

    public function unserialize(string $serialized): mixed
    {
        $allowedClasses = Arr::wrap(config(
            'metafields.unserialize_allowed_class', []
        ));

        return unserialize($serialized, [
            'allowed_classes' => $allowedClasses,
        ]);
    }
}
