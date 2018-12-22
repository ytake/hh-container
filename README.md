# This Project Has Been Deprecated
# HH-Container
simple light weight service location / dependency injection container

[![Build Status](https://travis-ci.org/ytake/hh-container.svg?branch=master)](https://travis-ci.org/ytake/hh-container)

## Installation

```bash
$ hhvm $(which composer) require ytake/hh-container
```

```json
"require": {
  "hhvm": ">=3.24",
  "ytake/hh-container": "^1.0"
},
```

## Usage

```hack
$container = new \Ytake\HHContainer\FactoryContainer();
$container->set('testing', $container ==> 'testing');
$container->get('testing'); // return string
```

## Singleton or Prototype

default *prototype*

### SINGLETON

```hack
$container = new \Ytake\HHContainer\FactoryContainer();
$container->set('scope:singleton', $container ==> new \stdClass(), \Ytake\HHContainer\Scope::Singleton);
```

### PROTOTYPE

```hack
$container = new \Ytake\HHContainer\FactoryContainer();
$container->set('scope:prototype', $container ==> new \stdClass(), \Ytake\HHContainer\Scope::Prototype);
```

## Dependency Injection

### set parameters
sample class

```hack
final class MessageClass {
  public function __construct(
    protected string $message
  ) {}

  public function message(): string {
    return $this->message;
  }
}

final class MessageClient {
  public function __construct(
    protected MessageClass $message
  ) {}

  public function message(): MessageClass {
    return $this->message;
  }
}
```

### Inject

```hack
$container = new \Ytake\HHContainer\FactoryContainer();
$container->set('message.class', $container ==> new MessageClass('testing'));
$container->set(MessageClient::class, $container ==> {
  $instance = $container->get('message.class');
  invariant($instance instanceof MockMessageClass, 'error');
  new MessageClient($instance);
});
$instance = $container->get(MessageClient::class);
```

### callable
returns the value of a callable with parameters supplied at calltime.

```hack
final class TestingInvokable {
  public function __invoke(FactoryContainer $container): int {
    return 1;
  }
}

$container = new \Ytake\HHContainer\FactoryContainer();
$container->set(TestingInvokable::class, $container ==> 
  $container->callable(
    new \Ytake\HHContainer\Invokable(
      new TestingInvokable(), '__invoke', $container
    )
  )
);

```

## Use modules

```hack

use Ytake\HHContainer\ServiceModule;
use Ytake\HHContainer\FactoryContainer;

class ExampleModule extends ServiceModule {
  
  public function provide(FactoryContainer $container): void {
    $container->set('example', $container ==> new \stdClass());
  }
}

```

```hack
$container = new \Ytake\HHContainer\FactoryContainer();
$container->register(ExampleModule::class);
$container->lockModule();
```

## Service Factory

```hack
use Ytake\HHContainer\Scope;
use Ytake\HHContainer\ServiceFactory;
use Ytake\HHContainer\FactoryContainer;
use Ytake\HHContainer\FactoryInterface;

class StringFactory implements FactoryInterface {
  const type T = string;
  public function provide(FactoryContainer $_container): StringFactory::T {
    return 'testing';
  }
  public function scope(): Scope {
    return Scope::Singleton;
  }

  public function name(): string {
    return 'testing'
  }
}

$factory = new ServiceFactory(new FactoryContainer());
$factory->registerFactory(new StringFactory());
$factory->create('testing');
```
