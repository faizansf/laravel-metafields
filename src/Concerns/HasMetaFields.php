<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Concerns;

use BackedEnum;
use FaizanSf\LaravelMetafields\Facades\LaravelMetafields;
use FaizanSf\LaravelMetafields\Models\MetaField;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;

trait HasMetaFields
{
    public static bool $cacheEnabled = true;

    /**
     * Meta fields relation
     */
    public function metaFields(): MorphMany
    {
        return $this->morphMany(MetaField::class, config('metafields.model_column_name'));
    }

    public function getCacheTtl(): int|null
    {
        if(property_exists($this, 'ttl')){
            return $this->ttl;
        }

        return null;
    }

    public function hasMetaField(): bool
    {
        return LaravelMetafields::hasMetafields($this);
    }

    /**
     * Get a single meta field value
     */
    public function getMetaField(string|BackedEnum $key): mixed
    {
        return LaravelMetafields::getMetaFieldValue($this, $key);
    }

    /**
     * Get All Meta fields of a model in key=>value pair
     */
    public function getAllMetaFields(): Collection
    {
        return LaravelMetafields::getAllMetaFields($this);
    }

    /**
     * Set a Metafield value
     */
    public function setMetaField(string|BackedEnum $key, $value): MetaField
    {
        return LaravelMetafields::setMetaFieldValue($this, $key, $value);
    }

    /**
     * Delete Metafield by key
     */
    public function deleteMetaField(string|BackedEnum $key): bool
    {

    }

    public function scopeWithMetafields(string ...$keys): void
    {

    }
}
