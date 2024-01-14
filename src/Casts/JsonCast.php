<?php

namespace FaizanSf\LaravelMetafields\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use JsonException;

class JsonCast implements CastsAttributes
{
    /**
     * @throws JsonException
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
