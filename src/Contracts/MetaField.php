<?php

namespace FaizanSf\LaravelMetafields\Contracts;

interface MetaField
{
    public function getSerializer(): Serializer;

    public function getKey(): string;

}
