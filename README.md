# Cobalt

A lightweight application framework derived from the [Slim][slim]
framework, providing some common services such as Database
([PDO][pdo]) access, Logging ([Monolog][monolog]), Templates
([Twig][twig]) and many more.

[slim]: https://www.slimframework.com/
[pdo]: https://www.php.net/manual/en/book.pdo.php
[monolog]: https://seldaek.github.io/monolog/
[twig]: https://twig.symfony.com/

## Requirements

* PHP 8
* MySQL 5.7 / 8

## Installation

~~~
git clone ...
composer install
npm install
php cobalt.php make:env
echo "CREATE DATABASE cobalt CHARSET utf8mb4;" | mysql
~~~

Edit .env to suit adjusting the DB_xxx values

Import the schema
~~~
cat schema.sql| mysql cobalt
~~~

And create the superuser account
~~~
php cobalt.php make:superuser
~~~

## Run on a local development box

~~~
php cobalt.php serve [--address=127.0.0.1] [--port=8000]
~~~

(Re)compile CSS assets with
~~~
npm run dev|prod|watch
~~~
