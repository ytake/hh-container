<?hh // strict

use type Facebook\HackTest\HackTest;
use type Ytake\HHContainer\Scope;
use type Ytake\HHContainer\ServiceFactory;
use type Ytake\HHContainer\FactoryContainer;
use type Ytake\HHContainer\FactoryInterface;

use function Facebook\FBExpect\expect;

class FactoryTest extends HackTest {
  public function testShouldReturnExpectValues(): void {
    $factory = new ServiceFactory(new FactoryContainer());
    $factory->registerFactory(new StringFactory());
    expect($factory->create('testing'))->toBeSame('testing');
  }

  public function testShouldReturnStdClass(): void {
    $factory = new ServiceFactory(new FactoryContainer());
    $factory->registerFactory(new MockClassFactory());
    $i = $factory->create(MockClass::class);
    expect($i)->toBeInstanceOf(MockClass::class);
    if($i instanceof MockClass) {
      expect($i->getT())->toBeSame(1);
    }
    expect($factory->create(MockClass::class))->toBeInstanceOf(MockClass::class);
    expect($factory->create(MockClass::class))->toBeSame($factory->create(MockClass::class));
  }
}

class StringFactory implements FactoryInterface {
  const type T = string;

  public function provide(FactoryContainer $_container): this::T {
    return 'testing';
  }

  <<__Rx>>
  public function scope(): Scope {
    return Scope::SINGLETON;
  }

  <<__Rx>>
  public function name(): string {
    return 'testing';
  }
}

class MockClassFactory implements FactoryInterface {
  const type T = MockClass;

  public function provide(FactoryContainer $_container): this::T {
    return new MockClass(1);
  }

  <<__Rx>>
  public function scope(): Scope {
    return Scope::SINGLETON;
  }

  <<__Rx>>
  public function name(): string {
    return MockClass::class;
  }
}

class MockClass {
  public function __construct(private int $t) {}

  public function getT(): int {
    return $this->t;
  }
}
