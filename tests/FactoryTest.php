<?hh // strict

use Ytake\HHContainer\ServiceFactory;
use Ytake\HHContainer\FactoryContainer;
use Ytake\HHContainer\FactoryInterface;

class FactoryTest extends \PHPUnit\Framework\TestCase
{
  public function testShouldReturnExpectValues(): void
  {
    $factory = new ServiceFactory(new FactoryContainer());
    $factory->registerFactory(new StringFactory());
    $this->assertSame('testing', $factory->resolve('stringer'));
  }

  public function testShouldReturnStdClass(): void
  {
    $factory = new ServiceFactory(new FactoryContainer());
    $factory->registerFactory(new StdClassFactory());
    $this->assertInstanceOf(\stdClass::class, $factory->resolve(\stdClass::class));
  }
}

class StringFactory implements FactoryInterface {
  const type T = string;
  public function provide(FactoryContainer $container): this::T {
    return 'testing';
  }

  public function name(): string {
    return 'stringer';
  }
}

class StdClassFactory implements FactoryInterface {
  const type T = stdClass;
  public function provide(FactoryContainer $container): this::T {
    return new \stdClass();
  }

  public function name(): string {
    return 'stdClass';
  }
}
