<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class StandardCast implements CastsAttributes
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
