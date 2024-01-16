<?php

namespace FaizanSf\LaravelMetafields\Contracts;

interface ValueSerializer
{
    public function unserialize($serialized): mixed;

    public function serialize($value): string;
}
