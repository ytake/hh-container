<?hh // strict

use type Ytake\HHContainer\FactoryContainer;
use type Ytake\HHContainer\ServiceModule;

final class ContainerTest extends \PHPUnit\Framework\TestCase
{
  public function testShouldReturnPrimitiveTypes(): void
  {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->set(
      'testing',
      $container ==> 'testing'
    );
    $this->assertSame('testing', $container->get('testing'));

    $container->set('testing123', $container ==> 1);
    $this->assertSame(1, $container->get('testing123'));

    $container->set('testing:testing', $container ==> true);
    $this->assertSame(true, $container->get('testing:testing'));
  }

  public function testShouldReturnSingletonObject(): void
  {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->set(
      'testing:testing',
      $container ==> new \stdClass(),
      \Ytake\HHContainer\Scope::Singleton
    );
    $this->assertInstanceOf(\stdClass::class, $container->get('testing:testing'));
    $this->assertSame($container->get('testing:testing'), $container->get('testing:testing'));
  }

  public function testShouldReturnPrototypeObject(): void
  {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->set('testing:testing', $container ==> new \stdClass(), \Ytake\HHContainer\Scope::Prototype);
    $this->assertInstanceOf(\stdClass::class, $container->get('testing:testing'));
    $this->assertNotSame($container->get('testing:testing'), $container->get('testing:testing'));
  }

  public function testShouldReturunResolveInstance(): void
  {
      $container = new \Ytake\HHContainer\FactoryContainer();
      $container->set('testing', $container ==> 1);
      $container->set(
        'testing:testing',
        $container ==> $container->get('testing'),
        \Ytake\HHContainer\Scope::Prototype
      );
      $this->assertSame(1, $container->get('testing:testing'));
  }

  public function testShouldThrowException() :void
  {
    $this->expectException(\Ytake\HHContainer\NotFoundException::class);
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->get('testing');
  }

  /**
   * @expectedException \Ytake\HHContainer\NotFoundException
   */
  public function testShouldReturnProvideInstance(): void
  {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->registerModule(StubModule::class);
    $container->lockModule();
    $this->assertInstanceOf(\stdClass::class, $container->get('provide:sample'));
    $container->set('message.class', $container ==>  new MockMessageClass('testing'));
    $container->get('message.class');
  }

  public function testShouldResolveInstance(): void
  {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $this->assertInstanceOf(\stdClass::class, $container->get(\stdClass::class));
    $container->parameters(ResolvedObject::class, 'object', $container ==> new \stdClass());
    $container->parameters(ResolvedObject::class, 'integer', $container ==> 100);
    $instance = $container->get(ResolvedObject::class);
    $this->assertInstanceOf(ResolvedObject::class, $instance);
  }

  public function testShouldResolveConstructorPromotionInstance(): void
  {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->parameters(ConstructorPromotionClass::class, 'object', $container ==> new \stdClass());
    $container->parameters(ConstructorPromotionClass::class, 'integer', $container ==> 100);
    $instance = $container->get(ConstructorPromotionClass::class);
    $this->assertInstanceOf(ConstructorPromotionClass::class, $instance);
    if ($instance instanceof ConstructorPromotionClass) {
      $this->assertSame(100, $instance->getInteger());
    }
  }

  public function testShouldResolveDependencyInjectionWithLocation(): void
  {
    $container = new FactoryContainer();
    $container->set('message.class', $container ==>  new MockMessageClass('testing'));
    $container->parameters(MessageClient::class, 'message', $container ==> $container->get('message.class'));
    $instance = $container->get(MessageClient::class);
    if ($instance instanceof MessageClient) {
      $this->assertSame('testing', $instance->message()->message());
    }
  }
}

class StubModule extends ServiceModule {
  <<__Override>>
  public function provide(FactoryContainer $container): void {
    $container->set(\stdClass::class, $container ==> new \stdClass());
  }
}

class ResolvedObject
{
  private \stdClass $object;
  private int $integer;
  public function __construct(\stdClass $object, int $integer = 1)
  {
    $this->object = $object;
    $this->integer = $integer;
  }
}

class ConstructorPromotionClass
{
  public function __construct(private \stdClass $object, private int $integer)
  {

  }

  public function getInteger(): int {
    return $this->integer;
  }
}

class MockMessageClass {
  public function __construct(protected string $message) {
  }
  public function message(): string {
    return $this->message;
  }
}

final class MessageClient {
  public function __construct(protected MockMessageClass $message) {

  }
  public function message(): MockMessageClass {
    return $this->message;
  }
}
