<?php

namespace FaizanSf\LaravelMetafields\Tests\TestSupport;

use FaizanSf\LaravelMetafields\Concerns\HasMetaFields;
use FaizanSf\LaravelMetafields\Contracts\MetaFields;
use Illuminate\Database\Eloquent\Model;

class Car extends Model implements MetaFields
{
    use HasMetaFields;
}
