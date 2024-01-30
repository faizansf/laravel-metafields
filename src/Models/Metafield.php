<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Models;

use Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property $key
 * @property $value
 */
class Metafield extends Model
{
    /**
     * Mass assignable attributes
     *
     * @var array<int, string>
     */
    protected $fillable = ['key', 'value'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array<int, string>
     */
    protected $hidden = ['model_type', 'model_id'];


    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('metafields.table'));
    }
}
