<?php

return [
    'table' => 'meta_fields',

    'model_column_name' => 'model',

    'unserialize_allowed_class' => [],

    'value_serializer' => \FaizanSf\LaravelMetafields\Dependencies\Serializers\StandardSerializer::class,

    //'value_serializer' => \FaizanSf\LaravelMetafields\Dependencies\Serializers\JsonSerializer::class

    'cache_enabled' => true,

    'cache_key_prefix' => 'MetaField',

    'cache_ttl' => null, //forever
];
