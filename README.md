# HH-Container

<<<<<<<

simple light weight service location / dependency injection container

[![Build Status](https://travis-ci.org/ytake/hh-container.svg?branch=develop
)](https://travis-ci.org/ytake/hh-container)

## Installation

```bash
$ hhvm --php $(which composer) require ytake/hh-container
```

```json
"require": {
  "hhvm": ">=3.11.0",
  "ytake/hh-container": "~0.0"
},
```

## Usage

```php
$container = new \Ytake\HHContainer\FactoryContainer();
$container->set('testing', $container ==> 'testing');
$container->get('testing'); // return string
```

## Singleton or Prototype

default *prototype*

### SINGLETON

```php
$container = new \Ytake\HHContainer\FactoryContainer();
$container->set('scope:singleton', $container ==> new \stdClass(), \Ytake\HHContainer\Scope::SINGLETON);
```

### PROTOTYPE

```php
$container = new \Ytake\HHContainer\FactoryContainer();
$container->set('scope:prototype', $container ==> new \stdClass(), \Ytake\HHContainer\Scope::PROTOTYPE);
```

## Dependency Injection

### set parameters

```php
$container->parameters(
  'string className',
  'parameter name',
  $container ==> 'parameter value'
);
```

sample class
```php
final class MessageClass {
  public function __construct(protected string $message) {
  }
  public function message(): string {
    return $this->message;
  }
}

final class MessageClient {
  public function __construct(protected MessageClass $message) {

  }
  public function message(): MessageClass {
    return $this->message;
  }
}
```

### Inject

```php
$container = new \Ytake\HHContainer\FactoryContainer();
$container->set('message.class', $container ==> new MessageClass('testing'));
$container->parameters(MessageClient::class, 'message', $container ==> $container->get('message.class'));
$instance = $container->get(MessageClient::class);
```

## Use modules

```php

use Ytake\HHContainer\ServiceModule;
use Ytake\HHContainer\FactoryContainer;

class ExampleModule extends ServiceModule
{
  public function provide(FactoryContainer $container): void
  {
    $container->set('example', $container ==> new \stdClass());
  }
}

```

```php
$container = new \Ytake\HHContainer\FactoryContainer();
$container->register(ExampleModule::class);
$container->lockModule();
```
