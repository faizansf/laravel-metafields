<?php

namespace FaizanSf\LaravelMetafields\Tests\TestSupport;

use FaizanSf\LaravelMetafields\Concerns\HasMetaFields;
use FaizanSf\LaravelMetafields\Contracts\MetaFieldable;
use Illuminate\Database\Eloquent\Model;

class Car extends Model implements MetaFieldable
{
    use HasMetaFields;
}
