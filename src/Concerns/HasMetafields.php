<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Concerns;

use BackedEnum;
use FaizanSf\LaravelMetafields\DataTransferObjects\NormalizedKey;
use FaizanSf\LaravelMetafields\Exceptions\DuplicateKeyException;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Exceptions\InvalidValueSerializerException;
use FaizanSf\LaravelMetafields\Exceptions\ModelNotSetException;
use FaizanSf\LaravelMetafields\LaravelMetafields;
use FaizanSf\LaravelMetafields\Models\Metafield;
use FaizanSf\LaravelMetafields\Proxies\WithoutCacheLaravelMetafieldsProxy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Symfony\Component\Yaml\Exception\DumpException;

/**
 * @method static registerSerializers()
 */
trait HasMetafields
{
    /**
     * Optional property to be defined in your model. When set, it overrides the default caching strategy for the model.
     * If true, caching is enabled; if false, caching is disabled.
     */
    protected ?bool $shouldCacheMetafields = null;

    /**
     * Optional property to be defined in your model. Specifies the time-to-live (TTL) for the cache in seconds.
     * Overrides the default cache TTL value for the model.
     * Only applicable if caching is enabled.
     */
    protected ?int $ttl = null;

    /**
     * Optional property to be defined in your model. Specifies the serializer for the metafield
     * @var array
     */
    protected array $metafieldSerializers = [];

    /**
     * Core Metafield Service
     * @var LaravelMetafields|null
     */
    protected ?LaravelMetafields $metafieldsService = null;

    public function initializeHasMetafields(): void
    {
        if (method_exists($this, 'registerSerializers')) {
            $this->registerSerializers();
        }
    }

    /**
     * @return MorphMany
     */
    public function metafields(): MorphMany
    {
        return $this->morphMany(Metafield::class, config('metafields.model_column_name'));
    }

    /**
     * @return WithoutCacheLaravelMetafieldsProxy
     */
    public function withoutCache(): WithoutCacheLaravelMetafieldsProxy
    {
        return $this->getMetafieldsService()->withoutCache();
    }

    /**
     * @param string|BackedEnum $key
     * @param $default
     * @return mixed
     * @throws InvalidKeyException
     * @throws ModelNotSetException
     * @throws InvalidValueSerializerException
     */
    public function getMetafield(string|BackedEnum $key, $default = null): mixed
    {
        return $this->getMetafieldsService()->get($key, $default);
    }

    /**
     * @return Collection
     * @throws InvalidKeyException
     * @throws InvalidValueSerializerException
     * @throws ModelNotSetException
     */
    public function getAllMetafields(): Collection
    {
        return $this->getMetafieldsService()->getAll();
    }

    /**
     * @param string|BackedEnum $key
     * @param $value
     * @return string
     * @throws InvalidKeyException
     * @throws InvalidValueSerializerException
     * @throws ModelNotSetException
     */
    public function setMetafield(string|BackedEnum $key, $value): string
    {
        return $this->getMetafieldsService()->set($key, $value);

    }

    /**
     * @param string|BackedEnum $key
     * @return bool
     * @throws InvalidKeyException
     * @throws ModelNotSetException
     */
    public function deleteMetafield(string|BackedEnum $key): bool
    {
        return $this->getMetafieldsService()->delete($key);
    }

    /**
     * @return int
     * @throws ModelNotSetException
     */
    public function deleteAllMetafields(): int
    {
        return $this->getMetafieldsService()->deleteAll();
    }

    /**
     * @return bool
     */
    public function shouldCacheMetafields(): bool
    {
        return $this->shouldCacheMetafields ?? config('metafields.cache_metafields');
    }

    /**
     * @param string|BackedEnum $key
     * @param string $value
     * @return void
     * @throws DuplicateKeyException
     * @throws InvalidKeyException
     * @throws InvalidValueSerializerException
     * @throws ModelNotSetException
     */
    public function mapSerializer(string|BackedEnum $key, string $value): void
    {
        /** @var NormalizedKey $normalizedKey */
        [$normalizedKey, $serializer] = $this->getMetafieldsService()->getNormalizedKeyWithValidSerializer($key, $value);

        $normalizedKey  = $normalizedKey->asString();

        if (array_key_exists($normalizedKey, $this->metafieldSerializers)) {
            throw DuplicateKeyException::withMessage($normalizedKey, $serializer);
        }

        $this->metafieldSerializers[$normalizedKey] = $serializer;
    }

    /**
     * Returns the TTL for the cache in seconds.
     * @return int|null
     */
    public function getTtl(): ?int
    {
        return $this->ttl ?? config('metafields.cache_ttl');
    }

    /**
     * @param NormalizedKey $key
     * @return mixed
     */
    public function getValueSerializer(NormalizedKey $key): mixed
    {
        return $this->metafieldSerializers[$key->asString()] ?? null;
    }

    protected function getMetafieldsService(): LaravelMetafields {
        if ($this->metafieldsService === null) {
            $this->metafieldsService = app(LaravelMetafields::class);
            $this->metafieldsService->setModel($this);
        }
        return $this->metafieldsService;
    }

}
