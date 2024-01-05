<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Contracts;

interface KeyGenerator
{
    public function generate(string $key): string;
}
