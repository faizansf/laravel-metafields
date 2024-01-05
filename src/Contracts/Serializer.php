<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Contracts;

interface Serializer
{
    public function serialize($value): string;

    public function unserialize(string $serialized): mixed;
}
