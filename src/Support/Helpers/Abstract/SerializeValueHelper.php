<?php

namespace FaizanSf\LaravelMetafields\Support\Abstract;

use BackedEnum;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use FaizanSf\LaravelMetafields\Exceptions\InvalidKeyException;
use FaizanSf\LaravelMetafields\Exceptions\InvalidValueSerializerException;
use FaizanSf\LaravelMetafields\Exceptions\ModelNotSetException;

use Illuminate\Support\Facades\App;

abstract class SerializeValueHelper
{
    public function __construct(protected NormalizeMetaKeyHelper $keyNormalizer)
    {}
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
     * @param string|BackedEnum $key The key for which to resolve the serializer.
     * @return ValueSerializer|null Returns an instance of the resolved serializer or default serializer.
     * @throws InvalidValueSerializerException
     * @throws ModelNotSetException
     * @throws InvalidKeyException
     */
    public function resolve(Metafieldable $model, string|BackedEnum $key): ValueSerializer
    {
        $key = $this->keyNormalizer->normalize($key);

        $serializerClass = $model->getValueSerializer($key) ?? ValueSerializer::class;

        if (!$this->isValidSerializer($serializerClass)) {
            throw InvalidValueSerializerException::withMessage($serializerClass);
        }

        return App::make($serializerClass);
    }

}
