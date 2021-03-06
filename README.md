# Method Persister/Cacher [ ![Codeship Status for forceedge01/method-cacher](https://app.codeship.com/projects/dade2ef0-ad48-0135-c2b8-0e745ba13322/status?branch=master)](https://app.codeship.com/projects/257185)

What is this?
=============

This library allows caching the result of methods for a specified amount of time. This can be on a global level or per user session.

Installation
============

Using composer:

```bash
composer require "genesis/method-persister"
```

Instantiation
=============

```php
namespace ABC\Example;

use Genesis\MethodPersister;

// Ideally done using a DI library.
$centralStoragePath = '/tmp/cache/';
$persistenceRepository = new MethodPersister\PersistenceRepository($centralStoragePath);
$persister = new MethodPersister\Persister($persistenceRepository);
```

Usage
=====

Consider your code like this:

```php
$result = $myObject->myMethod($arg1, $arg2);

return $result;
```

The above can be rewritten with the cacher as follows yielding the same but faster results.

```php
$result = $persister->persist($myObject, 'myMethod')
	->withParameters($arg1, $arg2)
	->overAPeriodOf('+10 seconds')
	->execute();

// Use result as normal
return $result;
```

This will persist the cache for 10 seconds from the first call, after that the cache will be refreshed. The default storage method is centralised but can be changed to distributed i.e session caching using the `->in()` method.

```php
$result = $persister->persist($myObject, 'userSpecificData')
    ->withParameters($arg1, $arg2)
    ->overAPeriodOf('+10 seconds')
    ->in(PersisterInterface::STATE_DISTRIBUTE)
    ->execute();
```

Contributions
=============

This project is in its early stages, forks are welcome :)

Tests
=====

Library is tested using phpunit. To run tests first install dependencies using composer then run command:

```bash
make
```