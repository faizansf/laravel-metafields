<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\ValueSerializers;

use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use JsonException;

class JsonValueSerializer implements ValueSerializer
{
    /**
     * @throws JsonException
     */
    public function unserialize($serialized): mixed
    {
        return json_decode($serialized, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function serialize($value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
