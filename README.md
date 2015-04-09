# CakePHP-Basic-Seed

[![Latest Version](https://img.shields.io/github/release/loadsys/CakePHP-Basic-Seed.svg?style=flat-square)](https://github.com/loadsys/CakePHP-Basic-Seed/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/loadsys/cakephp-basic-seed.svg?style=flat-square)](https://packagist.org/packages/loadsys/cakephp-basic-seed)

Provides a simple mechanism for seeding data into your CakePHP application's database.

## Requirements

* PHP 5.3+
* CakePHP 2.0+

## Installation

### Composer

````bash
$ composer require loadsys/cakephp-basic-seed:~1.0
````

## Usage

* Add this plugin to your application by adding this line to your bootstrap.php

````php
CakePlugin::load('BasicSeed');
````

This is a command line plugin, to use:

````bash
Console/cake BasicSeed.seed` or `Console/cake BasicSeed.seed --dev
````

This runs the `Config/seed.php` and --dev runs `Config/seed_dev.php` seed file

You can also specify `--file` and it will use the file specified (inside of `Config/`)

````bash
Console/cake BasicSeed.seed --file seed_staging.php
````

Will use the file located at `Config/seed_staging.php`

To create a `seed.php` file

````bash
Console/cake BasicSeed.seed init
````

To create a `seed_dev.php` file

````bash
Console/cake BasicSeed.seed init --dev
````

To create a custom seed file, you can use the `--file` parameter.

````bash
Console/cake BasicSeed.seed init --file seed_staging.php
````

## Contributing

### Reporting Issues

Please use [GitHub Isuses](https://github.com/loadsys/CakePHP-Basic-Seed/issues) for listing any known defects or issues.

### Development

When developing this plugin, please fork and issue a PR for any new development.

## License ##

[MIT](https://github.com/loadsys/CakePHP-Basic-Seed/blob/master/LICENSE.md)

## Copyright ##

[Loadsys Web Strategies](http://www.loadsys.com) 2015
