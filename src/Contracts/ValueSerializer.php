<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Contracts;

interface ValueSerializer
{
    public function unserialize($serialized): mixed;

    public function serialize($value): mixed;
}
