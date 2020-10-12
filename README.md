# Simple/SQL

[![Build Status](https://img.shields.io/travis/simpl-php/sql.svg?style=flat-square)](https://travis-ci.org/simpl-php/sql)

## A dead-simple layer on top of PDO to make PDO setup and querying simpler

This component makes it easy to make and execute SQL statements with PDO.

You can run parameterized queries in a single step instead of doing separate prepare and execute statements.

This will automatically convert your query to a prepared statement with parameterized queries to prevent nasty SQL injection attacks.


## Installation

```bash
composer require simpl/sql
```

## Usage

### Connecting to the database.
```php
$db = new \Simpl\SQL('localhost', 'your-db-name', 'your-username', 'your-password');
```

### Running a SELECT query with parameters.
```php

$res = $db->query('select * from test where foo = ? or bar = ?', [$foo, $bar]);
```

> Since this is just a wrapper around PDO, you'll get back a `\PDOStatement` object you can then operate on as you normally would.

See <https://simpl-php.com/components/sql> for full documentation.

## Coding Standards
This library uses [PHP_CodeSniffer](http://www.squizlabs.com/php-codesniffer) to ensure coding standards are followed.

I have adopted the [PHP FIG PSR-2 Coding Standard](http://www.php-fig.org/psr/psr-2/) EXCEPT for the tabs vs spaces for indentation rule. PSR-2 says 4 spaces. I use tabs. No discussion.

To support indenting with tabs, I've defined a custom PSR-2 ruleset that extends the standard [PSR-2 ruleset used by PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer/blob/master/CodeSniffer/Standards/PSR2/ruleset.xml). You can find this ruleset in the root of this project at PSR2Tabs.xml


### Codesniffer

```bash
composer codensiffer
```

### Codefixer

```bash
composer codefixer
```