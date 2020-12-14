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
