<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Exceptions;

use Exception;

class InvalidConfigurationException extends Exception
{
    public static function withMessage($message): self
    {
        return new self($message);
    }
}
