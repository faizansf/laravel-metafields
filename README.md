# Laravel Metafields

[![Latest Version on Packagist](https://img.shields.io/packagist/v/faizansf/laravel-metafields.svg?style=flat-square)](https://packagist.org/packages/faizansf/laravel-metafields)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/faizansf/laravel-metafields/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/faizansf/laravel-metafields/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/faizansf/laravel-metafields/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/faizansf/laravel-metafields/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)

The Laravel Metafields package is a versatile and powerful tool designed for Laravel developers who need to extend their
models with metafield functionality.
This package enables you to effortlessly attach additional custom fields
(metafields) to any Eloquent model in your Laravel application,
providing a seamless way to enhance your models with
extra data without altering your database schema.

## Use Cases:

This package is ideal for projects that require additional data storage like CMS, e-commerce platforms, and custom CRM
systems. It's particularly useful in scenarios where the database schema needs to remain unchanged while still allowing
for data extension.

## Installation

You can install the package via composer:

```bash
composer require faizansf/laravel-metafields
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-metafields-migrations"
php artisan migrate
```
>**Note:**<br/>
> Before running the migrations make sure you have set the correct configuration

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-metafields-config"
```

## Configuration

```php
return [
    // The name of the database table to store metafields.
    'table' => 'metafields',

    // The name of the column in the 'meta_fields' table that references the model.
    'model_column_name' => 'model',

    // An array of classes that are allowed to be unserialized.
    'unserialize_allowed_class' => [],

    // The class responsible for serializing the values stored in metafields.
    'default_serializer' => \FaizanSf\LaravelMetafields\Support\ValueSerializers\StandardValueSerializer::class,

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
```

## Integrating in Model
Integrate the `Metafiedable` contract and the `HasMetafields` trait into your model

```php
...
use FaizanSf\LaravelMetafields\Concerns\HasMetafields;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;

class ExampleModel extends Model implements Metafieldable
{
    use HasMetafields;
     
    ...
}
```

## Usage

To set a metafield, use string or string backed enum key and value as below:

```php
$person = Person::find(1);

//using HasMetafields trait
$person->setMetafield('some-key', 'value')
```

To get metafield value use:

```php
//using HasMetafields trait
$person->getMetafield('some-key');
$person->getAllMetafields();
```
You can also provide a default value when getting a metafield

```php
//using HasMetafields trait
$person->getMetafield('some-key', 'default value');
$person->getAllMetafields(['some-key' => 'default value']);
```

>**Note:**<br/>
>A default value is _not_ persisted in the database and is just returned whenever the actual value is null

Similarly, metafields can be deleted as follows:
```php
//using HasMetafields trait
$person->deleteMetafield('some-key');
$person->deleteAllMetaField('some-key');
```

### Cache

Caching is enabled by default, but can be disabled in your `metafields.php` configuration file. To control caching
behavior in your model class, add the `$shouldCacheMetafields` property. Setting this property in your model will 
override the default caching configuration. Additionally, you can specify a custom time-to-live (TTL) for the 
cache by adding the `$ttl` property to your model, allowing for fine-tuned cache duration control.

<br/>

In the `metafields.php` config file

```php
[
    ...
    // Flag to enable or disable caching of metafields.
    'cache_metafields' => true,
    ...
]
```

Or in your model class

```php
...
class Person extends Model implements Metafieldable
{
    use HasMetafields;
    
    protected $shouldCacheMetafields = true;
    protected $ttl = 600
}
```
<br/>

You can retrieve a non-cached version of the data by using the `withoutCache()` method. This method provides a
straightforward way to bypass caching for a single call, ensuring you get the most up-to-date data.

```php
//using HasMetafields trait
$person->withoutCache()->getMetafield('some-key');
$person->withoutCache()->getAllMetafields();
```

### Serialization

The package includes `StandardValueSerializer`, `DirectValueSerializer` and `JsonValueSerializer` classes.
You can choose a default serializer for all fields in the `metafields.php` configuration file.
Additionally, you can define
a `$metafieldSerializers` array inside your model, or you can implement a static `registerSerializers()` method 
in your model to override the default serialization behavior.
The `registerSerializers()` method will then use `mapSerializer()` method provided by `HasMetafields` trait 
to register the serializers.
Any custom serializer class you add must 
implement the `FaizanSf\LaravelMetafields\Contracts\ValueSerializer` interface.

<br/>

```php
namespace App\ValueSerializers;

use FaizanSf\LaravelMetafields\Contracts\ValueSerializer;
use Illuminate\Support\Arr;

class CustomSerializer implements ValueSerializer
{
    public function unserialize($serialized): mixed
    {
        //Do some custom logic here
    }

    public function serialize($value): string
    {
        //Do some custom logic here
    }
}
```

And then in your model

```php
...
class Person extends Model implements Metafieldable
{
    use HasMetafields;
    
    protected static function registerSerializers(){
        $this
            ->mapSerializer('some-key', CustomSerializer::class)
            ->mapSerializer(PersonMetafieldsEnum::Name, CustomSerializer::class)
    }
}
```

Alternatively, you can also define `$metafieldSerializers` property directly into your model

```php
...
class Person extends Model implements Metafieldable
{
    use HasMetafields;
    
    protected array $metafieldSerializers  = [
         'my-custom-metafield' => CustomSerializer::class,
         ExampleMetafieldsEnum::ExampleField->value => CustomSerializer::class
    ];
}
```

>**Note:**<br/>
>Due to PHP's restriction where enums can't be used as array keys, we need to
utilize the enum values for mapping serializers.

<br/>

In situations where you already possess a string value that doesn't require serialization, the `DirectValueSerializer`
can be used.
This allows you to bypass the usual serialization process, streamlining the handling of such
pre-formatted or non-serializable values.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Faizan Shakil Faruqi](https://github.com/faizansf)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
