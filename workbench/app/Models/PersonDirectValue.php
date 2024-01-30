<?php

namespace Workbench\App\Models;

use FaizanSf\LaravelMetafields\Concerns\HasMetafields;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use FaizanSf\LaravelMetafields\Support\ValueSerializers\DirectValueSerializer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Workbench\Database\Factories\PersonDirectValueFactory;
use Workbench\Database\Factories\PersonFactory;

class PersonDirectValue extends Model implements Metafieldable
{
    use HasFactory;
    use HasMetafields;

    protected function registerSerializers(): void
    {
        $this->mapSerializer('test-key', DirectValueSerializer::class);
    }

    protected static function newFactory(): PersonDirectValueFactory|Factory
    {
        return PersonDirectValueFactory::new();
    }
}
