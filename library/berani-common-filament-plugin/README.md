# This is my package berani-common-filament-plugin

[![Latest Version on Packagist](https://img.shields.io/packagist/v/beranidigital/berani-common-filament-plugin.svg?style=flat-square)](https://packagist.org/packages/beranidigital/berani-common-filament-plugin)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/beranidigital/berani-common-filament-plugin/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/beranidigital/berani-common-filament-plugin/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/beranidigital/berani-common-filament-plugin/fix-php-code-styling.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/beranidigital/berani-common-filament-plugin/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/beranidigital/berani-common-filament-plugin.svg?style=flat-square)](https://packagist.org/packages/beranidigital/berani-common-filament-plugin)



This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require beranidigital/berani-common-filament-plugin
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="berani-common-filament-plugin-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="berani-common-filament-plugin-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="berani-common-filament-plugin-views"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$beraniCommonFilamentPlugin = new BeraniDigitalID\BeraniCommonFilamentPlugin();
echo $beraniCommonFilamentPlugin->echoPhrase('Hello, BeraniDigitalID!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Yusuf Sekhan Althaf](https://github.com/Ticlext-Altihaf)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
