<?php

namespace FaizanSf\LaravelMetafields\Exceptions;

use Exception;

class InvalidKeyException extends Exception
{
    public static function withMessage(mixed $key): self
    {
        return new self(
            'Expected key to be string or string backed Enum Got'.gettype($key)
        );
    }
}
