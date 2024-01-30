<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Exceptions;

use Exception;

class DuplicateKeyException extends Exception
{
    public static function withMessage($normalizedKey, $serializer): self
    {
        return new self($normalizedKey.' is already mapped to '.$serializer);
    }
}
