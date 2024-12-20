# App Init Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fuelviews/app-init.svg?style=flat-square)](https://packagist.org/packages/fuelviews/app-init)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/fuelviews/app-init/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/fuelviews/app-init/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/fuelviews/app-init/fix-php-code-style-issues.yml?label=code%20style&style=flat-square)](https://github.com/fuelviews/app-init/actions?query=workflow%3A"Fix+PHP+code+style+issues")
[![Total Downloads](https://img.shields.io/packagist/dt/fuelviews/app-init.svg?style=flat-square)](https://packagist.org/packages/fuelviews/app-init)

## Installation

You can install the package via composer:

```bash
composer require --dev fuelviews/app-init
```

You can initialize the package using the installation command:

```bash
php artisan app-init:install
```

You can force initialize the package using the installation command:


```bash
php artisan app-init:install --force
```

You can process images in the ```public/images/``` folder using the installation command:

```bash
php artisan app-init:images
```

You can force process images in the ```public/images/``` folder using the installation command:

```bash
php artisan app-init:images --force
```


Optional: Install the image manipulation binaries on MacOS (using Homebrew):

```bash
brew install jpegoptim
brew install optipng
brew install pngquant
npm install -g svgo
brew install gifsicle
brew install webp
brew install libavif
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

- [Thejmitchener](https://github.com/thejmitchener)
- [Fuelviews](https://github.com/fuelviews)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
