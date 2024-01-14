<?php

namespace FaizanSf\LaravelMetafields\Contracts;

use BackedEnum;
use FaizanSf\LaravelMetafields\Models\Metafield;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|Metafield[] $metaFields
 */
interface Metafieldable
{
    public function metaFields(): MorphMany;

    /**
     * Get a single meta field value
     */
    public function getMetaField(string|BackedEnum $key): mixed;

    /**
     * Get All Meta fields of a model in key=>value pair
     */
    public function getAllMetaFields(): Collection;

    /**
     * Set a Metafield value
     */
    public function setMetaField(string|BackedEnum $key, $value): Metafield;

    /**
     * Delete Metafield by key
     */
    public function deleteMetaField(string|BackedEnum $key): bool;
}
