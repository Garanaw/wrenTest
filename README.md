<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Install

The application is based in Laravel. That means that a `.env` file has to be created (it can be copied from `.env.example` and then modified with your credentials). Also, `composer install` command has to be run.

The existence of a database is assumed, and in it, the initial `SQL` file should be migrated, as the migrations only modify the tables and their structures.

# Usage

This application does not have a browser interface, so only commands can be run.

The command to parse the given `CSV` file is as follows:

```shell
php artisan product:bulkImport /path/to/the/file
```

The `CSV` file doesn't have to be in the `Storage` folder, it can be read from anywhere in the machine, as long as the executor has permissions (UNIX machines). The path to the file can be absolute or relative to the working directory, Laravel will take care of finding it.

# Caveats

PHP 8 is necessary to run this application (not that it couldn't be done with PHP 7, but I wanted to test some new features such as `match` and the new `mixed` keyword).

# Some Decisions taken

In the `CSV` file, there are some fields with different formats, such as `price` or `discontinued`. Here I'll explain why I've decided to handle them in certain ways:

## Price

Initially, every field is passed as a `string`, even if they represent other types. This is the case of `price`. It can have different formats (with/without currency, currency at the beginning or end...). To handle the possible situations, the field is treated as a string when it's received, to ensure that we can remove the currency symbol. Once it's clean, the value is casted to a `float(8, 2)` value, so it can be inserted in the database.

## Discontinued

This field can contain a date, a boolean (understanding for "boolean" any affirmation/negation) or nothing. If we have a date (none present in the example `CSV`), it will be parsed, and "discontinued_at" will be considered `true`. If a boolean comes in the value, and this boolean is an affirmation, we understand that the product is considered "discontinued" from `now`, therefore that date is added. If the boolean is a negation or "empty", we understand that the product is not discontinued, therefore `null` will be passed.

# The intentional error in the product with code `P0017`

Although this error is intentional, it is a mistake that can happen easily when working with Excel (press `tab` instead of `enter` or in the middle of a sentence). This is treated as a "common case", and it's handled as follows:

- First, we check if the value has the intended type. If it does, continue as normal.
- If the value doesn't match the intended type, we consider it a "mistake", and treat this value as part of the previous value.
- The following pairs key/value are modified in runtime, so we can use the value from the next column (supposedly the intended value for the current column) as if it was the "current" one.
