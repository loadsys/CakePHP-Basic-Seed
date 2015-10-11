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

## Writing a Seed

The plugin provides a number of helper methods to help get data into your application more efficiently. The most notable of these is `$this->importTables($data)`. This method takes an array structure and iterates over it, converting the array data into Entities and saving them into each named table. The generate structure of the array is as follows:

```php
<?php
/**
 * Example BasicSeed plugin data seed file.
 *
 * Typically in `config/seed.php` or `config/seed_dev.php`.
 */

namespace App\Config\BasicSeed;

// Write your data import statements here.

$data = [
	'TableName' => [              // The proper name of the Table into which
	                              // the records will be imported.

		//'_truncate' => true,    // When enabled, ALL existing records will
		                          // be removed from the table before loading!

		//'_options' => [         // This array is passed to Table::newEntity().
		//	'validate' => false,  // Can be used to disable validation.
		//],

		'_defaults' => [          // Provide default values that will be merged
		                          // into each record before the Entity is
		                          // created. Can be used to reduce pointless
		                          // repetition in imported records.
		    'is_active' => true,
		],

		[                         // Everything else is counted as a separate
		                          // record to import. Remember that combined
		                          // with [_defaults], you only need to specify
		                          // the **unique** fields for each record.
			'id' => 1,
			'name' => 'record 1',
		],
	],
];

$this->importTables($data);

```

Remember that the seed file is just an extension of the BasicSeedShell and that seeds do not _have_ to conform to the above structure. You have access to anything you could normally do from inside a Shell, so for example, this is also a valid seed file:

```php
<?php
/**
 * Another example BasicSeed plugin data seed file.
 */

namespace App\Config\BasicSeed;

$Posts = $this->loadModel('Posts');
$posts = [
	['id' => 1, 'title' => 'Foo', 'body' => 'Lorem ipsum.'],
	['id' => 2, 'title' => 'Bar', 'body' => 'The meaning of life is 42.'],
];

foreach ($posts as $p) {
	$entity = $Posts->newEntity($p); // Careful, validation is still on!
	if($Posts->save($entity)) {
		$this->out("Saved {$entity->id}");
	} else {
		$this->warning("Save failed where title = {$entity->title}");
	}
}

```

...although it's worth point out that `::importTables()` performs a more robust version of this exact process for you, including dumping validation errors when they are encountered.


## Contributing

### Reporting Issues

Please use [GitHub Isuses](https://github.com/loadsys/CakePHP-Basic-Seed/issues) for listing any known defects or issues.

### Development

When developing this plugin, please fork and issue a PR for any new development.


## License ##

[MIT](https://github.com/loadsys/CakePHP-Basic-Seed/blob/master/LICENSE.md)


## Copyright ##

[Loadsys Web Strategies](http://www.loadsys.com) 2015
