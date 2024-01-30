<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields;

use BackedEnum;
use Closure;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use FaizanSf\LaravelMetafields\DataTransferObjects\NormalizedKey;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Exceptions\InvalidValueSerializerException;
use FaizanSf\LaravelMetafields\Exceptions\ModelNotSetException;
use FaizanSf\LaravelMetafields\Models\Metafield;
use FaizanSf\LaravelMetafields\Proxies\WithoutCacheLaravelMetafieldsProxy;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\MetaCacheHelper;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\NormalizeMetaKeyHelper;
use FaizanSf\LaravelMetafields\Support\Helpers\Abstract\SerializeValueHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class LaravelMetafields
{
    private bool $temporaryDisableCache = false;

    private ?Metafieldable $model = null;

    private NormalizeMetaKeyHelper $keyNormalizer;

    private SerializeValueHelper $serializeValueHelper;

    private MetaCacheHelper $cacheHelper;

    public function __construct(
        NormalizeMetaKeyHelper $keyNormalizer,
        SerializeValueHelper $serializeValueHelper,
        MetaCacheHelper $cacheHelper
    ) {
        $this->keyNormalizer = $keyNormalizer;
        $this->serializeValueHelper = $serializeValueHelper;
        $this->cacheHelper = $cacheHelper;
    }

    public function setModel(Metafieldable $model): self
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set a Metafield
     *
     * @throws ModelNotSetException
     * @throws InvalidKeyException
     * @throws InvalidValueSerializerException
     */
    public function set(string|BackedEnum $key, $value): mixed
    {
        $this->ensureModelIsValid();

        $normalizedKey = $this->normalizeKey($key);

        $serializedValue = $this->serialize($normalizedKey, $value);

        /** @var $metafield Metafield */
        $this->model->metafields()->updateOrCreate(
            ['key' => $normalizedKey],
            ['value' => $serializedValue]
        );

        $this->clearCacheByKeys([$normalizedKey, $this->normalizeAllMetafieldsKey()]);

        return $value;
    }

    /**
     * @throws ModelNotSetException
     * @throws InvalidKeyException
     * @throws InvalidValueSerializerException
     */
    public function get(string|BackedEnum $key, mixed $default = null): mixed
    {
        $this->ensureModelIsValid();

        $normalizedKey = $this->normalizeKey($key);

        return $this->runCachedOrDirect(function () use ($normalizedKey, $default) {
            $metafieldRow = $this->getMetafieldRow($normalizedKey);

            if ($metafieldRow && isset($metafieldRow->value)) {
                return $this->unserialize($normalizedKey, $metafieldRow->value);
            }

            return $default ?? null;
        }, $normalizedKey);
    }

    /**
     * Get All the metafields
     *
     * @throws ModelNotSetException
     * @throws InvalidValueSerializerException
     * @throws InvalidKeyException
     */
    public function getAll(): Collection
    {
        $this->ensureModelIsValid();

        return $this->runCachedOrDirect(function () {
            return $this->getAllMetafieldRows()->mapWithKeys(function (Metafield $metafield) {
                $normalizedKey = $this->normalizeKey($metafield->key);

                $value = $this->unserialize($normalizedKey, $metafield->value);

                return [$metafield->key => $value ?? null];
            });
        }, $this->normalizeAllMetafieldsKey());
    }

    /**
     * Delete a metafield by key
     *
     * @throws ModelNotSetException
     * @throws InvalidKeyException
     */
    public function delete(string|BackedEnum $key): bool
    {
        $this->ensureModelIsValid();

        $normalizedKey = $this->normalizeKey($key);

        $metafield = $this->getMetafieldRow($normalizedKey);

        if ($metafield) {
            $metafield->delete();

            $this->clearCacheByKeys([
                $normalizedKey, $this->normalizeAllMetafieldsKey(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Delete all metafields of a given model
     *
     * @throws ModelNotSetException
     */
    public function deleteAll(): int
    {
        $this->ensureModelIsValid();

        $metafieldKeys = $this->model->metafields()->pluck('key')->map(function ($key) {
            return $this->normalizeKey($key);
        })->toArray();

        $delete = $this->model->metafields()->delete();

        $this->clearCacheByKeys($metafieldKeys);
        $this->clearCacheByKey($this->normalizeAllMetafieldsKey());

        return $delete;

    }

    /**
     * Normalize the given key and map it with the given serializer after validation
     *
     * @param string|BackedEnum $key
     * @param string $serializer
     * @return array
     *
     * @throws InvalidKeyException
     * @throws InvalidValueSerializerException
     * @throws ModelNotSetException
     */
    public function getNormalizedKeyWithValidSerializer(string|BackedEnum $key, string $serializer): array
    {
        $this->ensureModelIsValid();

        $normalizedKey = $this->normalizeKey($key);

        if (! $this->serializeValueHelper->isValidSerializer($serializer)) {
            throw new InvalidValueSerializerException($serializer);
        }

        return [$normalizedKey, $serializer];
    }

    public function setTemporaryDisableCache($temporaryDisabledCache): void
    {
        $this->temporaryDisableCache = $temporaryDisabledCache;
    }

    /**
     * For Testing purposes
     */
    public function unsetModel(): void
    {
        $this->model = null;
    }

    /**
     * Temporarily disable cache for current call
     */
    public function withOutCache(): WithoutCacheLaravelMetafieldsProxy
    {
        return new WithoutCacheLaravelMetafieldsProxy($this);
    }

    /**
     * @throws ModelNotSetException
     */
    private function clearCacheByKey(NormalizedKey $key): void
    {
        $this->ensureModelIsValid();

        $this->cacheHelper->clear($this->model, $key);
    }

    /**
     * Clear cache of multiple keys.
     *
     * @param  NormalizedKey[]  $keys  Array of NormalizedKey objects.
     *
     * @throws ModelNotSetException
     */
    private function clearCacheByKeys(array $keys): void
    {
        foreach ($keys as $normalizedKey) {
            $this->clearCacheByKey($normalizedKey);
        }
    }

    /**
     * Ensure that the model is valid
     *
     * @throws ModelNotSetException
     */
    private function ensureModelIsValid(): void
    {
        if (! $this->model) {
            throw ModelNotSetException::withMessage();
        }
    }

    /**
     * @throws ModelNotSetException
     */
    private function getMetafieldRow(NormalizedKey $key): Model|null
    {
        $this->ensureModelIsValid();

        return $this->model->metafields()->where('key', $key)->first();
    }

    private function getAllMetafieldRows(): Collection
    {
        $this->ensureModelIsValid();

        return $this->model->metafields()->get();
    }

    /**
     * Checks if cache is enabled and the current instance is configured to use it.
     */
    private function canUseCache(): bool
    {
        return $this->model->shouldCacheMetafields() && ! $this->temporaryDisableCache;
    }

    /**
     * Executes the given closure and caches its result for the given time if cache is enabled.
     */
    private function runCachedOrDirect(Closure $callback, NormalizedKey $key): mixed
    {
        return $this->canUseCache()
            ? Cache::remember(
                $this->cacheHelper->getCacheKey($this->model, $key),
                $this->model->getTtl(),
                $callback)
            : $callback();
    }

    /**
     * @throws InvalidKeyException
     */
    private function normalizeKey(string|BackedEnum $key, bool $ignoreKeyValidation = false): DataTransferObjects\NormalizedKey
    {
        return $this->keyNormalizer->normalize($key, $ignoreKeyValidation);
    }

    /**
     * @throws InvalidKeyException
     */
    private function normalizeAllMetafieldsKey(): NormalizedKey
    {
        return $this->normalizeKey(config('metafields.all_metafields_cache_key'), true);
    }

    /**
     * Resolve serializer for the given metafield key
     *
     * @throws InvalidValueSerializerException
     */
    private function resolveSerializer(NormalizedKey $key): ValueSerializer
    {
        return $this->serializeValueHelper->resolve($this->model, $key);
    }

    /**
     * @throws InvalidValueSerializerException
     */
    private function serialize(NormalizedKey $key, $value): string
    {
        return $this->resolveSerializer($key)
            ->serialize($value);
    }

    /**
     * Unserializes Value
     *
     * @throws InvalidValueSerializerException
     */
    private function unserialize(NormalizedKey $key, $value): mixed
    {
        return $this->resolveSerializer($key)
            ->unserialize($value);
    }
}
