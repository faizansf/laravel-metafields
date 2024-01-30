<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Contracts;

use BackedEnum;
use FaizanSf\LaravelMetafields\Models\Metafield;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection|Metafield[] $metafields
 */
interface Metafieldable
{
    public function metafields(): MorphMany;

    /**
     * Get a single meta field value
     */
    public function getMetafield(string|BackedEnum $key): mixed;

    /**
     * Get All Meta fields of a model in key=>value pair
     */
    public function getAllMetafields(): Collection;

    /**
     * Set a Metafield value
     */
    public function setMetafield(string|BackedEnum $key, $value): string;

    /**
     * Delete Metafield by key
     */
    public function deleteMetafield(string|BackedEnum $key): bool;
}
