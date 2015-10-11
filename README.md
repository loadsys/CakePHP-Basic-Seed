# CakePHP-Basic-Seed

[![Latest Version](https://img.shields.io/github/release/loadsys/CakePHP-Basic-Seed.svg?style=flat-square)](https://github.com/loadsys/CakePHP-Basic-Seed/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/loadsys/cakephp-basic-seed.svg?style=flat-square)](https://packagist.org/packages/loadsys/cakephp-basic-seed)

Provides a simple mechanism for seeding data into your CakePHP application's database.

## Requirements

* PHP 5.6+
* CakePHP 3.0+

:warning: Check the [cake-2.x branch](https://github.com/loadsys/CakePHP-Basic-Seed/tree/cake-2.x) for the CakePHP v2.x compatible version. The `1.x.x` semver series maintains compatibility with CakePHP 2, while `~2` tracks CakePHP 3.


## Installation

### Composer

````bash
$ composer require loadsys/cakephp-basic-seed:~2.0
````

## Usage

* Add this plugin to your application by adding this line to your `bootstrap.php`:

````php
Plugin::load('BasicSeed', ['bootstrap' => false, 'routes' => false]);
````

This is a command line plugin. To use it:

````bash
bin/cake BasicSeed.basic_seed
# Runs the `config/seed.php` seed file.

# or
bin/cake BasicSeed.basic_seed --dev

# Runs `config/seed_dev.php` seed file.
````

You can also specify `--file` and it will use the file specified (inside of `config/`)

````bash
bin/cake BasicSeed.basic_seed --file seed_staging.php
# Will use the file located at `config/seed_staging.php`.
# This option always overrides --dev.
````


To create a `seed.php` file, run the `init` command:

````bash
bin/cake BasicSeed.basic_seed init
````

To create a `seed_dev.php` file:

````bash
bin/cake BasicSeed.basic_seed init --dev
````

To create a custom seed file, you can use the `--file` parameter.

````bash
bin/cake BasicSeed.basic_seed init --file seed_staging.php
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
