# Headacke

[![Build Status](https://travis-ci.org/ytake/headacke.svg?branch=develop
)](https://travis-ci.org/ytake/headacke)

simple light weight service location container

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
$container->set('testing', function ($container) {
  return 'testing';
});
$container->get('testing'); // return string
```

## Singleton or Prototype

default *prototype*

### SINGLETON

```php
$container = new \Headacke\FactoryContainer();
$container->set('scope:singleton', function ($container) {
  return new \stdClass();
}, \Headacke\Scope::SINGLETON);
```

### PROTOTYPE

```php
$container = new \Headacke\FactoryContainer();
$container->set('scope:prototype', function ($container) {
  return new \stdClass();
}, \Headacke\Scope::PROTOTYPE);
```

## Use modules

```php

use Headacke\ServiceModule;
use Headacke\FactoryContainer;

class ExampleModule extends ServiceModule
{
  public function provide(FactoryContainer $container): void
  {
    $container->set('example', function ($container) {
      $class = new \stdClass();
      return $class;
    });
  }
}

```

```php
$container = new \Headacke\FactoryContainer();
$container->register(ExampleModule::class);
$container->lockModule();
```
