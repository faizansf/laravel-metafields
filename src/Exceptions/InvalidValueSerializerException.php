<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Exceptions;

use Exception;
use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;

class InvalidValueSerializerException extends Exception
{
    public static function withMessage(?string $serializerClass): self
    {
        return new self(
            'Provided serializer must implements '.ValueSerializer::class.'. got '.$serializerClass.' instead'
        );
    }
}
