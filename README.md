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

This will persist the cache for 10 seconds from the first call, after that the cache will be refreshed.

Contributions
=============

This project is in its early stages, forks are welcome :)

Tests
=====

Library is tested using phpunit. To run tests first install dependencies using composer then run command:

```bash
phpunit -c tests
```