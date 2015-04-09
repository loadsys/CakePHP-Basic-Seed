# CakePHP-Basic-Seed

[![Latest Version](https://img.shields.io/github/release/loadsys/CakePHP-Basic-Seed.svg?style=flat-square)](https://github.com/loadsys/CakePHP-Basic-Seed/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/loadsys/cakephp-basic-seed.svg?style=flat-square)](https://packagist.org/packages/loadsys/cakephp-basic-seed)

Provides a simple mechanism for seeding data into an application's database.

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

`Console/cake BasicSeed.seed` or `Console/cake BasicSeed.seed --dev`

regular runs the Config/seed.php and --dev runs Config/seed_dev.php

you can also specify --file and it will use whatever you specify (inside of Config)

`Console/cake BasicSeed.seed --file seed_staging.php` uses Config/seed_staging.php

`Console/cake BasicSeed.seed init` or `Console/cake BasicSeed.seed init --dev` or `Console/cake BasicSeed.seed init --file seed_staging.php` will create the files in your app's `Config/`

## Contributing

### Reporting Issues

Please use [GitHub Isuses](https://github.com/loadsys/CakePHP-Basic-Seed/issues) for listing any known defects or issues.

### Development

When developing this plugin, please fork and issue a PR for any new development.

## License ##

[MIT](https://github.com/loadsys/CakePHP-Basic-Seed/blob/master/LICENSE.md)

## Copyright ##

[Loadsys Web Strategies](http://www.loadsys.com) 2015
