<?php

namespace FaizanSf\LaravelMetafields\Models;

use BackedEnum;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasMetaFields
{
    /**
     * Meta fields relation
     * @return MorphMany
     */
    public function metaFields(): MorphMany
    {
        return $this->morphMany(MetaField::class, config('metafields.model_column_name'));
    }

    /**
     * Get a single meta field value
     * @param string|BackedEnum $key
     * @return string
     */
    public function getMetaField(string|BackedEnum $key): string
    {

    }

    /**
     * Get All Meta fields of a model in key=>value pair
     * @return Collection
     */
    public function getAllMetaFields(): Collection
    {

    }

    /**
     * Set a Metafield value
     * @param string|BackedEnum $key
     * @param $value
     * @return bool
     */
    public function setMetaField(string|BackedEnum $key, $value): bool
    {

    }

    /**
     * Delete Metafield by key
     * @param string|BackedEnum $key
     * @return bool
     */
    public function deleteMetaField(string|BackedEnum $key): bool
    {

    }

}
