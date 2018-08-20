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
    $this->assertSame('testing', $factory->create(StringFactory::class));
  }

  public function testShouldReturnStdClass(): void
  {
    $factory = new ServiceFactory(new FactoryContainer());
    $factory->registerFactory(new MockClassFactory());
    $i = $factory->create(MockClassFactory::class);
    $this->assertSame(1, $i->getT());
    $this->assertInstanceOf(MockClass::class, $factory->create(MockClassFactory::class));
    $this->assertSame($factory->create(MockClassFactory::class), $factory->create(MockClassFactory::class));
  }
}

class StringFactory implements FactoryInterface {
  const type T = string;
  public function provide(FactoryContainer $_container): StringFactory::T {
    return 'testing';
  }
  public function scope(): Scope {
    return Scope::Singleton;
  }
}

class MockClassFactory implements FactoryInterface {
  const type T = MockClass;

  public function provide(FactoryContainer $_container): this::T {
    return new MockClass(1);
  }

  public function scope(): Scope {
    return Scope::Singleton;
  }
}

class MockClass {
  public function __construct(private int $t) {}

  public function getT(): int {
    return $this->t;
  }
}
