<?php

namespace Workbench\App\Models;

use FaizanSf\LaravelMetafields\Concerns\HasMetafields;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Workbench\Database\Factories\PersonFactory;

class Person extends Model implements Metafieldable
{
    use HasFactory;
    use HasMetafields;

    protected static function newFactory(): PersonFactory|Factory
    {
        return PersonFactory::new();
    }
}
