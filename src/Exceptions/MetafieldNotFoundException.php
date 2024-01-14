<?php

namespace FaizanSf\LaravelMetafields\Exceptions;

use Exception;

class MetafieldNotFoundException extends Exception
{
    public static function withMessage(mixed $key): self
    {
        return new self(
            'Metafield Not found having key '.$key
        );
    }
}
