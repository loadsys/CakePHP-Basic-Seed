# CakePHP-Basic-Seed

Provides a simple mechanism for seeding data into an application's database.

## Usage

`Console/cake BasicSeed.seed` or `Console/cake BasicSeed.seed --dev`

regular runs the Config/seed.php and --dev runs Config/seed_dev.php

you can also specify --file and it will use whatever you specify (inside of Config)

`Console/cake BasicSeed.seed --file seed_staging.php` uses Config/seed_staging.php
