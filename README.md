# Init Package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fuelviews/laravel-init.svg?style=flat-square)](https://packagist.org/packages/fuelviews/laravel-init)
[![Total Downloads](https://img.shields.io/packagist/dt/fuelviews/laravel-init.svg?style=flat-square)](https://packagist.org/packages/fuelviews/laravel-init)

## Installation

You can install the package via composer:

```bash
composer require --dev fuelviews/laravel-init
```

You can force initialize the package using the installation command:


```bash
php artisan init:install --force
```

You can process images in the ```public/images/``` folder using the installation command:

```bash
php artisan init:images
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

- [Joshua Mitchener](https://github.com/thejmitchener)
- [Daniel Clark](https://github.com/sweatybreeze)
- [Fuelviews](https://github.com/fuelviews)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
