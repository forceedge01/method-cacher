What is this?
=============

This library allows caching the result of methods for a specified amount of time. This can be on a global level or per user session.

Installation
============

Using composer:

```bash
composer require "genesis/method-persister"
```

Usage
=====

```php
$persister->persist(
	    $object,
	    'method'
	)
	->withParameters($params)
	->overAPeriodOf('+10 seconds')
	->execute();
```