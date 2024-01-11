<?php

namespace FaizanSf\LaravelMetafields\Casts;

use Illuminate\Contracts\Database\Eloquent\Castable;

class MetaFieldCast implements Castable
{
    /**
     * Get the name of the caster class to use when casting from / to this cast target.
     *
     * @param  array<string, mixed>  $arguments
     */
    public static function castUsing(array $arguments): string
    {
        return 'json';
    }
}
