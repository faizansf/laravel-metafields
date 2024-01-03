<?php

namespace FaizanSf\LaravelMetafields\Models;

use FaizanSf\LaravelMetafields\Contracts\MetaField as MetaFieldContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MetaField extends Model implements MetaFieldContract
{
    /**
     * Mass assignable attributes
     * @var array<int, string>
     */
    protected $fillable = ['key', 'value'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array<int, string>
     */
    protected $hidden = ['model_type', 'model_id'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'model_id' => 'integer',
    ];

    /**
     * The model relationship.
     *
     * @return MorphTo
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('metafields.table'));
    }
}
