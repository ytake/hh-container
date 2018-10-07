<?hh // strict

use type Facebook\HackTest\HackTest;
use type Ytake\HHContainer\FactoryContainer;
use type Ytake\HHContainer\ServiceModule;
use function Facebook\FBExpect\expect;
use \Ytake\HHContainer\NotFoundException;

final class ContainerTest extends HackTest {

  public function testShouldReturnPrimitiveTypes(): void {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->set(
      'testing',
      $container ==> 'testing'
    );
    expect($container->get('testing'));
    $container->set('testing123', $container ==> 1);
    expect($container->get('testing123'))->toBeSame(1);

    $container->set('testing:testing', $container ==> true);
    expect($container->get('testing:testing'))->toBeTrue();
  }

  public function testShouldReturnSingletonObject(): void {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->set(
      'testing:testing',
      $container ==> new \stdClass(),
      \Ytake\HHContainer\Scope::SINGLETON
    );
    expect($container->get('testing:testing'))->toBeInstanceOf(\stdClass::class);
    expect($container->get('testing:testing'))->toBeSame($container->get('testing:testing'));
  }

  public function testShouldReturnPrototypeObject(): void {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->set('testing:testing', $container ==> new \stdClass(), \Ytake\HHContainer\Scope::PROTOTYPE);
    expect($container->get('testing:testing'))->toBeInstanceOf(\stdClass::class);
    expect($container->get('testing:testing'))->toBePHPEqual($container->get('testing:testing'));
  }

  public function testShouldReturunResolveInstance(): void {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->set('testing', $container ==> 1);
    $container->set(
      'testing:testing',
      $container ==> $container->get('testing'),
      \Ytake\HHContainer\Scope::PROTOTYPE
    );
    expect($container->get('testing:testing'))->toBeSame(1);
  }

  <<ExpectedException(NotFoundException::class)>>
  public function testShouldThrowException() :void {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->get('testing');
  }

  <<ExpectedException(NotFoundException::class)>>
  public function testShouldReturnProvideInstance(): void {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->registerModule(StubModule::class);
    $container->lockModule();
    expect($container->get('provide:sample'))->toBeInstanceOf(\stdClass::class);
    $container->set('message.class', $container ==>  new MockMessageClass('testing'));
    expect($container->get('message.class'))->toBeInstanceOf(MockMessageClass::class);
  }

  public function testShouldResolveInstance(): void {
    $container = new \Ytake\HHContainer\FactoryContainer();
    expect($container->get(\stdClass::class))->toBeInstanceOf(\stdClass::class);
    $container->parameters(ResolvedObject::class, 'object', $container ==> new \stdClass());
    $container->parameters(ResolvedObject::class, 'integer', $container ==> 100);
    $instance = $container->get(ResolvedObject::class);
    expect($instance)->toBeInstanceOf(ResolvedObject::class);
  }

  public function testShouldResolveConstructorPromotionInstance(): void {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->parameters(ConstructorPromotionClass::class, 'object', $container ==> new \stdClass());
    $container->parameters(ConstructorPromotionClass::class, 'integer', $container ==> 100);
    $instance = $container->get(ConstructorPromotionClass::class);
    expect($instance)->toBeInstanceOf(ConstructorPromotionClass::class);
    if ($instance instanceof ConstructorPromotionClass) {
      expect($instance->getInteger())->toBeSame(100);
    }
  }

  public function testShouldResolveDependencyInjectionWithLocation(): void {
    $container = new FactoryContainer();
    $container->set('message.class', $container ==>  new MockMessageClass('testing'));
    $container->parameters(MessageClient::class, 'message', $container ==> $container->get('message.class'));
    $instance = $container->get(MessageClient::class);
    if ($instance instanceof MessageClient) {
      expect($instance->message()->message())->toBeSame('testing');
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
