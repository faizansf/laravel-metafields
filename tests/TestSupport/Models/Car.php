<?php

namespace FaizanSf\LaravelMetafields\Tests\TestSupport\Models;

use FaizanSf\LaravelMetafields\Concerns\HasMetafields;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use FaizanSf\LaravelMetafields\Tests\TestSupport\Factories\CarFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model implements Metafieldable
{
    use HasMetafields;

    public static function newFactory(): CarFactory
    {
        return new CarFactory;
    }
}
