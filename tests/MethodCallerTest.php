<?hh // strict

use type Facebook\HackTest\HackTest;
use type Ytake\HHContainer\MethodCaller;
use type Ytake\HHContainer\FactoryContainer;

use function Facebook\FBExpect\expect;

class MethodCallerTest extends HackTest {

  public function testShouldReturnExpectValues(): void {
    $container = new \Ytake\HHContainer\FactoryContainer();
    $container->set(
      TestingInvokable::class,
      $container ==>
        $container->callable(new MethodCaller(new TestingInvokable(), '__invoke', $container))
    );
    $container->set(
      TestingInvokableTwo::class,
      $container ==>
        $container->callable(new MethodCaller(new TestingInvokableTwo(), 'execute'))
    );
    expect($container->get(TestingInvokable::class))->toBeSame(1);
    expect($container->get(TestingInvokableTwo::class))->toBeSame('testing');
  }
}

final class TestingInvokable {
  public function __invoke(FactoryContainer $_container): int {
    return 1;
  }
}

final class TestingInvokableTwo {
  public function execute(): string {
    return 'testing';
  }
}
