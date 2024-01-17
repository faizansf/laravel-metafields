# Laravel Metafields

[![Latest Version on Packagist](https://img.shields.io/packagist/v/faizansf/laravel-metafields.svg?style=flat-square)](https://packagist.org/packages/faizansf/laravel-metafields)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/faizansf/laravel-metafields/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/faizansf/laravel-metafields/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/faizansf/laravel-metafields/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/faizansf/laravel-metafields/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/faizansf/laravel-metafields.svg?style=flat-square)](https://packagist.org/packages/faizansf/laravel-metafields)

The Laravel Metafields package is a versatile and powerful tool designed for Laravel developers who need to extend their
models with metafield functionality. This package enables you to effortlessly attach additional custom fields (
metafields) to any Eloquent model in your Laravel application, providing a seamless way to enhance your models with
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

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-metafields-config"
```

## Usage

Implements `Metafiedable` Contract in your model and use `HasMetafields` trait

```php
namespace App\Models;

use FaizanSf\LaravelMetafields\Concerns\HasMetafields;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use Illuminate\Database\Eloquent\Model;

class ExampleModel extends Model implements Metafieldable
{
    use HasMetafields;
}
```

To set a metafield use `setMetafield()` method on your model:

```php
$exampleModel = ExampleModel::first();
$exampleModel->setMetafield('my-custom-metafield', 'some string');    
```

To get a metafield use `getMetafield()` method on your model:

```php
$exampleModel->getMetafield('my-custom-metafield');    
```

To get a Collection of all the metafields use `getAllMetafields()` method on your model:

```php
$exampleModel->getAllMetafields();    
```

You can also get a Collection of specific metafields by key using `getMetafields()` method on your model:

```php
$exampleModel->getMetafields(['my-custom-metafield', ExampleFieldEnum::ExampleField]);    
```

### Cache

Caching is enabled by default, but can be disabled in your `metafields.php` configuration file. To control caching
behavior
in your model class, add the `$shouldCacheMetafields` property. Setting this property in your model will override the
default caching configuration. Additionally, you can specify a custom time-to-live (TTL) for the cache by adding
the `$ttl` property to your model, allowing for fine-tuned cache duration control.
<br/><br/>
In the `metafields.php` config file

```php
[
    ...
    
    // Flag to enable or disable caching of meta fields.
    'cache_metafields' => true,
    
    ...
]
```

Or in your model class

```php
use FaizanSf\LaravelMetafields\Concerns\HasMetafields;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use Illuminate\Database\Eloquent\Model;

class ExampleModel extends Model implements Metafieldable
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
$exampleModel->withoutCache()->getMetafield('my-custom-metafield');   
```

### Serialization

The package includes `StandardValueSerializer` and `JsonValueSerializer` classes. You have the option to choose a
default serializer for all fields in the `metafields.php` configuration file. Additionally, you can define
a `$metafieldSerializers` array inside your model to override the default serialization behavior.
Any custom serializer class you add must implement the `FaizanSf\LaravelMetafields\Contracts\ValueSerializer` interface.

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

And then

```php
use FaizanSf\LaravelMetafields\Concerns\HasMetafields;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use Illuminate\Database\Eloquent\Model;
use App\Serializers\CustomSerializer;
use App\Enums\ExampleMetafieldsEnum;

class ExampleModel extends Model implements Metafieldable
{
    use HasMetafields;
    
    protected array $metafieldSerializers  = [
         'my-custom-metafield' => CustomSerializer::class,
         ExampleMetafieldsEnum::ExampleField => CustomSerializer::class
    ];
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Faizan Shakil Faruqi](https://github.com/faizansf)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
