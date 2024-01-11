<?php

namespace FaizanSf\LaravelMetafields\Concerns;

use FaizanSf\LaravelMetafields\Dependencies\Serializers\StandardSerializer;
use Illuminate\Support\Str;

trait IsMetaField
{
    public function getSerializer(): StandardSerializer
    {
        return new StandardSerializer();
    }

    public function getKey(): string
    {
        //return kebab case name of class
        return Str::kebab(__CLASS__);
    }

}
