<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\DataTransferObjects;

use FaizanSf\LaravelMetafields\Contracts\DataTransferObject;

final class NormalizedKey implements DataTransferObject
{
    public function __construct(public string $key){}

    public function __toString(): string
    {
        return $this->key;
    }

    public function asString(): string
    {
        return $this->key;
    }
}
