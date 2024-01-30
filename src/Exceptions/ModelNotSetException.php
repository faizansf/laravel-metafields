<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Exceptions;

class ModelNotSetException extends \Exception
{
    public static function withMessage(): self
    {
        return new self(
            'Model is not set. Specify a model using setModel() method'
        );
    }

}
