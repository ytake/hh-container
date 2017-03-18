# Headacke

<<<<<<<

simple light weight service location / dependency injection container

**H** ead **ack** e

[![Build Status](https://travis-ci.org/ytake/headacke.svg?branch=develop
)](https://travis-ci.org/ytake/headacke)

## Installation

```bash
$ hhvm --php $(which composer) require ytake/headacke
```

```json
"require": {
  "hhvm": ">=3.11.0",
  "ytake/headacke": "~0.0"
},
```

## Usage

```php
$container = new \Headacke\FactoryContainer();
$container->set('testing', $container ==> 'testing');
$container->get('testing'); // return string
```

## Singleton or Prototype

default *prototype*

### SINGLETON

```php
$container = new \Headacke\FactoryContainer();
$container->set('scope:singleton', $container ==> new \stdClass(), \Headacke\Scope::SINGLETON);
```

### PROTOTYPE

```php
$container = new \Headacke\FactoryContainer();
$container->set('scope:prototype', $container ==> new \stdClass(), \Headacke\Scope::PROTOTYPE);
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
$container = new \Headacke\FactoryContainer();
$container->set('message.class', $container ==> new MessageClass('testing'));
$container->parameters(MessageClient::class, 'message', $container ==> $container->get('message.class'));
$instance = $container->get(MessageClient::class);
```

## Use modules

```php

use Headacke\ServiceModule;
use Headacke\FactoryContainer;

class ExampleModule extends ServiceModule
{
  public function provide(FactoryContainer $container): void
  {
    $container->set('example', $container ==> new \stdClass());
  }
}

```

```php
$container = new \Headacke\FactoryContainer();
$container->register(ExampleModule::class);
$container->lockModule();
```
