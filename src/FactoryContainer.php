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

use Closure;
use Psr\Container\ContainerInterface;

enum Scope : int {
  Prototype = 0;
  Singleton = 1;
}

type TServiceModule = classname<ServiceModule>;

/**
 * simple light weight service locator container
 * not supported autowiring
 * @author yuuki.takezawa<yuuki.takezawa@comnect.jp.net>
 */
class FactoryContainer implements ContainerInterface {

  protected Map<string, Scope> $scopes = Map {};

  protected Vector<TServiceModule> $modules = Vector {};

  protected Map<string, (function(FactoryContainer): mixed)>
    $bindings = Map {};

  protected array<string, array<string, (function(FactoryContainer): mixed)>>
    $parameters = [];

  protected bool $locked = false;

  /**
   * supported closure only
   */
  public function set(
    string $id,
    (function(FactoryContainer): mixed) $callback,
    Scope $scope = Scope::Prototype,
  ): void {
    if (!$this->locked) {
      $this->bindings->add(Pair {$id, $callback});
      $this->scopes->add(Pair {$id, $scope});
    }
  }

  public function parameters(
    string $id,
    string $name,
    (function(FactoryContainer): mixed) $callback,
  ): void {
    if (!$this->locked) {
      $this->parameters[$id][$name] = $callback;
    }
  }

  public function get($id): mixed {
    if ($this->has($id)) {
      $resolved = $this->bindings->get($id);
      if (!\is_null($resolved)) {
        if ($this->scopes->get($id) === Scope::Singleton) {
          return $this->shared($id);
        }
        return \call_user_func($resolved, $this);
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
        \sprintf('Identifier "%s" is not binding.', $id),
      );
    }
    throw new ContainerException(\sprintf('Error retrieving "%s"', $id));
  }

  <<__Memoize>>
  protected function shared(string $id): mixed {
    return call_user_func($this->bindings->at($id), $this);
  }

  public function has($id): bool {
    return $this->bindings->containsKey($id);
  }

  public function bindings(
  ): Map<string, (function(FactoryContainer): mixed)> {
    return $this->bindings;
  }

  public function flush(): void {
    $this->bindings->clear();
    $this->scopes->clear();
    $this->locked = false;
  }

  public function remove(string $id): void {
    if (!$this->locked) {
      $this->bindings->removeKey($id);
      $this->scopes->removeKey($id);
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
      if (\array_key_exists($id, $this->parameters)) {
        if (\array_key_exists($parameter->getName(), $this->parameters[$id])) {
          $r->add(call_user_func(
            $this->parameters[$id][$parameter->getName()],
            $this,
          ));
        }
      }
    }
    return $r;
  }

  public function callable(Invokable $invokable): mixed {
    return $invokable->proceed();
  }
}
