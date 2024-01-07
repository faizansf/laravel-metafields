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

    public static function bootHasMetaFields(LaravelMetafields $metaField): void
    {}
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
    public function setMetaField(string|BackedEnum $key, $value): bool
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
