<?php

namespace FaizanSf\LaravelMetafields\Dependencies\KeyGenerators;

use FaizanSf\LaravelMetafields\Contracts\KeyGenerator as KeyGeneratorContract;

class KebabCaseKeyGenerator implements KeyGeneratorContract
{
    public function generate(string $key): string
    {
        return "";
    }
}
