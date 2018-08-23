<?hh

/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 *
 * Copyright (c) 2017-2018 Yuuki Takezawa
 *
 */
namespace Ytake\HHContainer;

use type Psr\Container\ContainerInterface;

enum Scope : int {
  PROTOTYPE = 0;
  SINGLETON = 1;
}

type TServiceModule = classname<ServiceModule>;
type TCallable = (function(FactoryContainer): mixed);

use function is_null;
use function call_user_func;
use function sprintf;
use function array_key_exists;

/**
 * simple light weight service locator container
 * not supported autowiring
 * @author yuuki.takezawa<yuuki.takezawa@comnect.jp.net>
 */
class FactoryContainer implements ContainerInterface {

  protected Vector<TServiceModule> $modules = Vector {};

  protected array<string, array<string, TCallable>>
    $parameters = [];

  protected bool $locked = false;

  protected Map<string, Map<Scope, TCallable>> $mapper = Map{};

  public function set(
    string $id,
    TCallable $callback,
    Scope $scope = Scope::PROTOTYPE,
  ): void {
    if (!$this->locked) {
      $this->mapper->add(Pair {$id, Map{$scope => $callback}});
    }
  }

  public function parameters(
    string $id,
    string $name,
    TCallable $callback,
  ): void {
    if (!$this->locked) {
      $this->parameters[$id][$name] = $callback;
    }
  }

  public function get($id): mixed {
    if ($this->has($id)) {
      $resolved = $this->mapper->get($id);
      if (!is_null($resolved)) {
        if ($resolved->firstKey() === Scope::SINGLETON) {
          return $this->shared($id);
        }
        $callable = $resolved->firstValue();
        if ($callable) {
          return call_user_func($callable, $this);
        }
      }
    }
    try {
      $reflectionClass = new \ReflectionClass($id);
      if ($reflectionClass->isInstantiable()) {
        $arguments = Vector{};
        $constructor = $reflectionClass->getConstructor();
        if ($constructor instanceof \ReflectionMethod) {
          $resolvedParameters = $this->resolveConstructorParameters($id, $constructor);
          if ($resolvedParameters->count()) {
            $arguments = $resolvedParameters;
          }
        }
        return $reflectionClass->newInstanceArgs($arguments);
      }
    } catch (\ReflectionException $e) {
      throw new NotFoundException(
        sprintf('Identifier "%s" is not binding.', $id),
      );
    }
    throw new ContainerException(sprintf('Error retrieving "%s"', $id));
  }

  <<__Memoize>>
  protected function shared(string $id): mixed {
    $shared = $this->mapper->at($id);
    $call = $shared->firstValue();
    if(!is_null($call)) {
      return call_user_func($call, $this);
    }
  }

  public function has($id): bool {
    return $this->mapper->containsKey($id);
  }

  public function bindings(
  ): ImmMap<string, Map<Scope, TCallable>> {
    return $this->mapper->toImmMap();
  }

  public function flush(): void {
    $this->mapper->clear();
    $this->locked = false;
  }

  public function remove(string $id): void {
    if (!$this->locked) {
      $this->mapper->removeKey($id);
    }
  }

  public function registerModule(TServiceModule $moduleClassName): void {
    if (!$this->locked) {
      $this->modules->add($moduleClassName);
    }
  }

  public function lockModule(): void {
    foreach ($this->modules->getIterator() as $iterator) {
      (new $iterator())->provide($this);
    }
    $this->locked = true;
  }

  protected function resolveConstructorParameters(
    string $id,
    \ReflectionMethod $constructor,
  ): Vector<mixed> {
    $r = Vector{};
    $parameters = $constructor->getParameters();
    foreach ($parameters as $parameter) {
      if (array_key_exists($id, $this->parameters)) {
        if (array_key_exists($parameter->getName(), $this->parameters[$id])) {
          $r->add(call_user_func(
            $this->parameters[$id][$parameter->getName()],
            $this,
          ));
        }
      }
    }
    return $r;
  }

  public function callable(MethodCallIntreface $invokable): mixed {
    return $invokable->proceed();
  }
}
