<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Contracts;

interface DataTransferObject
{
    public function asString(): string;
}
