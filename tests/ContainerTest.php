<?hh // strict

class ContainerTest extends \PHPUnit\Framework\TestCase
{
  public function testShouldReturnPrimitiveTypes(): void
  {
    $container = new \Headacke\FactoryContainer();
    $container->set('testing', function ($container) {
      return 'testing';
    });
    $this->assertSame('testing', $container->get('testing'));

    $container->set('testing123', function ($container) {
      return 1;
    });
    $this->assertSame(1, $container->get('testing123'));

    $container->set('testing:testing', function ($container) {
      return true;
    });
    $this->assertSame(true, $container->get('testing:testing'));
  }

  public function testShouldReturnSingletonObject(): void
  {
    $container = new \Headacke\FactoryContainer();
    $container->set('testing:testing', function ($container) {
      return new \stdClass();
    }, \Headacke\Scope::SINGLETON);
    $this->assertInstanceOf(\stdClass::class, $container->get('testing:testing'));
    $this->assertSame($container->get('testing:testing'), $container->get('testing:testing'));
  }

  public function testShouldReturnPrototypeObject(): void
  {
    $container = new \Headacke\FactoryContainer();
    $container->set('testing:testing', function ($container) {
      return new \stdClass();
    }, \Headacke\Scope::PROTOTYPE);
    $this->assertInstanceOf(\stdClass::class, $container->get('testing:testing'));
    $this->assertNotSame($container->get('testing:testing'), $container->get('testing:testing'));
  }

  public function testShouldReturunResolveInstance(): void
  {
      $container = new \Headacke\FactoryContainer();
      $container->set('testing', function ($container) {
        return 1;
      });
      $container->set('testing:testing', function ($container) {
        return $container->get('testing');
      }, \Headacke\Scope::PROTOTYPE);
      $this->assertSame(1, $container->get('testing:testing'));
  }

  public function testShouldThrowException() :void
  {
    $this->expectException(\Headacke\NotFoundException::class);
    $container = new \Headacke\FactoryContainer();
    $container->get('testing');
  }

  public function testShouldReturnProvideInstance(): void
  {
    $container = new \Headacke\FactoryContainer();
    $container->register(StubModule::class);
    $container->lockModule();
    $this->assertInstanceOf(\stdClass::class, $container->get('provide:sample'));
  }
}

class StubModule extends \Headacke\ServiceModule
{
  public function provide(\Headacke\FactoryContainer $container): void
  {
    $container->set('provide:sample', function ($container) {
      $class = new \stdClass();
      return $class;
    });
  }
}
