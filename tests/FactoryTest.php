<?hh // strict

use type Ytake\HHContainer\Scope;
use type Ytake\HHContainer\ServiceFactory;
use type Ytake\HHContainer\FactoryContainer;
use type Ytake\HHContainer\FactoryInterface;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
  public function testShouldReturnExpectValues(): void
  {
    $factory = new ServiceFactory(new FactoryContainer());
    $factory->registerFactory(new StringFactory());
    $this->assertSame('testing', $factory->create('testing'));
  }

  public function testShouldReturnStdClass(): void
  {
    $factory = new ServiceFactory(new FactoryContainer());
    $factory->registerFactory(new MockClassFactory());
    $i = $factory->create(MockClass::class);
    $this->assertInstanceOf(MockClass::class, $i);
    if($i instanceof MockClass) {
      $this->assertSame(1, $i->getT());
    }
    $this->assertInstanceOf(MockClass::class, $factory->create(MockClass::class));
    $this->assertSame($factory->create(MockClass::class), $factory->create(MockClass::class));
  }
}

class StringFactory implements FactoryInterface {
  const type T = string;
  public function provide(FactoryContainer $_container): this::T {
    return 'testing';
  }
  public function name(): string {
    return 'testing';
  }
  public function scope(): Scope {
    return Scope::SINGLETON;
  }
}

class MockClassFactory implements FactoryInterface {
  const type T = MockClass;

  public function provide(FactoryContainer $_container): this::T {
    return new MockClass(1);
  }

  public function scope(): Scope {
    return Scope::SINGLETON;
  }

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
