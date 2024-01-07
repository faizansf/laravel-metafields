<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Dependencies\Serializers;

use FaizanSf\LaravelMetafields\Contracts\Serializer as SerializerContract;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class StandardSerializer implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        $allowedClasses = Arr::wrap(config(
            'metafields.unserialize_allowed_class', []
        ));

        return unserialize($value, [
            'allowed_classes' => $allowedClasses,
        ]);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        return serialize($value);
    }
}
