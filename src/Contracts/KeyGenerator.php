<?php

namespace FaizanSf\LaravelMetafields\Contracts;

interface KeyGenerator
{
    public function generate(string $key): string;
}
