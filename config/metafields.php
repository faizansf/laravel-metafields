<?php

return [

    // The name of the database table to store meta fields.
    'table' => 'meta_fields',

    // The name of the column in the 'meta_fields' table that references the model.
    'model_column_name' => 'model',

    // An array of classes that are allowed to be unserialized.
    'unserialize_allowed_class' => [],

    // The class responsible for serializing the values stored in meta fields.
    'value_cast' => \FaizanSf\LaravelMetafields\Casts\StandardCast::class,

    // Flag to enable or disable caching of meta fields.
    'cache_enabled' => true,

    // Time-to-live for cached meta fields. Null indicates caching forever.
    'cache_ttl' => null,

    // The prefix used for cache keys when storing individual meta field values.
    'cache_key_prefix' => 'LaravelMetafields',
];
