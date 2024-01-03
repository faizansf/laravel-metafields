<?php

namespace FaizanSf\LaravelMetafields\Models;

use BackedEnum;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasMetaFields
{
    /**
     * Meta fields relation
     */
    public function metaFields(): MorphMany
    {
        return $this->morphMany(MetaField::class, config('metafields.model_column_name'));
    }

    /**
     * Get a single meta field value
     */
    public function getMetaField(string|BackedEnum $key): string
    {

    }

    /**
     * Get All Meta fields of a model in key=>value pair
     */
    public function getAllMetaFields(): Collection
    {

    }

    /**
     * Set a Metafield value
     */
    public function setMetaField(string|BackedEnum $key, $value): bool
    {

    }

    /**
     * Delete Metafield by key
     */
    public function deleteMetaField(string|BackedEnum $key): bool
    {

    }
}
