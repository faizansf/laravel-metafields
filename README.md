# Laravel Metafields

[![Latest Version on Packagist](https://img.shields.io/packagist/v/faizansf/laravel-metafields.svg?style=flat-square)](https://packagist.org/packages/faizansf/laravel-metafields)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/faizansf/laravel-metafields/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/faizansf/laravel-metafields/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/faizansf/laravel-metafields/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/faizansf/laravel-metafields/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/faizansf/laravel-metafields.svg?style=flat-square)](https://packagist.org/packages/faizansf/laravel-metafields)

The Laravel Metafields package is a versatile and powerful tool designed for Laravel developers who need to extend their models with metafield functionality. This package enables you to effortlessly attach additional custom fields (metafields) to any Eloquent model in your Laravel application, providing a seamless way to enhance your models with extra data without altering your database schema.

## Use Cases:

This package is ideal for projects that require additional data storage like CMS, e-commerce platforms, and custom CRM systems. It's particularly useful in scenarios where the database schema needs to remain unchanged while still allowing for data extension.
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

You can set your metafields like this

```php
$exampleModel = ExampleModel::first();
$exampleModel->setMetafield('my-custom-metafield', 'some string');    
```

You can get your metafields like this

```php
$exampleModel->getMetafield('my-custom-metafield');    
```

You can get all metafields in model instance like this
```php
$exampleModel->getAllMetafields();    
```
<br/>
Caching is enabled by default can be disabled in your metafields configuration file.
<br/>

Caching can also be enabled or disabled based on your model class. In your model class add the following properties and that will override the default configuration

```php
namespace App\Models;

use FaizanSf\LaravelMetafields\Concerns\HasMetafields;
use FaizanSf\LaravelMetafields\Contracts\Metafieldable;
use Illuminate\Database\Eloquent\Model;

class ExampleModel extends Model implements Metafieldable
{
    use HasMetafields;
    
    protected $cacheMetaFields = false;
    protected $ttl = 600
}
```

You can also directly retrieve a non-cached version by using `withOutcache()` method. The method employs the proxy pattern to facilitate the easy bypassing of caching with a single call.

```php
$exampleModel->withoutCache()->getAllMetafields();   
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
