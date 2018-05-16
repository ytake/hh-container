<?hh // strict

use Ytake\HHContainer\Invokable;
use Ytake\HHContainer\FactoryContainer;

class InvokableTest extends \PHPUnit\Framework\TestCase
{
  public function testShouldReturnExpectValues(): void
  {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->set(TestingInvokable::class, $container ==>
      $container->callable(new Invokable(new TestingInvokable(), '__invoke', $container))
    );
    $container->set(TestingInvokableTwo::class, $container ==>
      $container->callable(new Invokable(new TestingInvokableTwo(), 'execute'))
    );
    $this->assertSame(1, $container->get(TestingInvokable::class));
    $this->assertSame('testing', $container->get(TestingInvokableTwo::class));
  }
}

final class TestingInvokable {
  public function __invoke(FactoryContainer $container): int {
    return 1;
  }
}

final class TestingInvokableTwo {
  public function execute(): string {
    return 'testing';
  }
}
