<?php

use FaizanSf\LaravelMetafields\Support\ValueSerializers\StandardValueSerializer;

return [

    // The name of the database table to store meta fields.
    'table' => 'metafields',

    // The name of the column in the 'meta_fields' table that references the model.
    'model_column_name' => 'model',

    // An array of classes that are allowed to be unserialized by StandardValueSerializer.
    'unserialize_allowed_class' => [],

    // The class responsible for serializing the values stored in metafields.
    'default_serializer' => StandardValueSerializer::class,

    // Flag to enable or disable caching of metafields.
    'cache_metafields' => true,

    // Time-to-live for cached meta fields. Null indicates caching forever.
    'cache_ttl' => null,

    // The prefix used for cache keys when storing individual metafield values.
    'cache_key_prefix' => 'LaravelMetafields',

    //Block keys from being used as metafield keys
    'not_allowed_keys' => [],

    //Cache key for all metafields collection
    'all_metafields_cache_key' => 'all-metafields'
];
