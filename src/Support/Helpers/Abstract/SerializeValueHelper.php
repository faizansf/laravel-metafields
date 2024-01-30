<?php

declare(strict_types=1);

namespace FaizanSf\LaravelMetafields\Support\Helpers\Abstract;

use BackedEnum;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use FaizanSf\LaravelMetafields\DataTransferObjects\NormalizedKey;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Exceptions\InvalidValueSerializerException;
use FaizanSf\LaravelMetafields\Exceptions\ModelNotSetException;
use Illuminate\Support\Facades\App;

abstract class SerializeValueHelper
{
    protected array $serializerInstances = [];

    /**
     * Checks if the provided class name is a valid serializer.
     * @param string $serializerClass
     * @return bool
     */
    public function isValidSerializer(string $serializerClass): bool
    {
        if ($serializerClass === ValueSerializer::class) {
            return true;
        }

        return class_exists($serializerClass) &&
            in_array(ValueSerializer::class,
                class_implements($serializerClass), true);
    }

    /**
     * Resolves the appropriate serializer for a given key.
     *
     * @param Metafieldable $model
     * @param NormalizedKey $key The key for which to resolve the serializer.
     * @return ValueSerializer Returns an instance of the resolved serializer or default serializer.
     * @throws InvalidValueSerializerException
     */
    public function resolve(Metafieldable $model, NormalizedKey $key): ValueSerializer
    {
        $serializerClass = $model->getValueSerializer($key) ?? ValueSerializer::class;

        return $this->make($serializerClass);
    }

    /**
     * Makes the Serializer instance
     * @param string $serializerClass
     * @return ValueSerializer
     * @throws InvalidValueSerializerException
     */
    public function make(string $serializerClass): ValueSerializer
    {
        if (!$this->isValidSerializer($serializerClass)) {
            throw InvalidValueSerializerException::withMessage($serializerClass);
        }

        return $this->serializerInstances[$serializerClass] ??= App::make($serializerClass);
    }

}
