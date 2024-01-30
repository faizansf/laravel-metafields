<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Exceptions;

use Illuminate\Support\Arr;

class BadMethodCallException extends \Exception
{
    public static function withMessage($methodName, $allowedMethods = []): self
    {
        return new self("Method {$methodName} does not exist or is
        not allowed to be run without cache. Allowed methods are: ".Arr::join($allowedMethods, ','));
    }
}
