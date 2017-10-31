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
 * Copyright (c) 2017 Yuuki Takezawa
 *
 */
namespace Ytake\HHContainer;

use Closure;
use Psr\Container\ContainerInterface;

enum Scope: int {
  PROTOTYPE = 0;
  SINGLETON = 1;
}

/**
 * simple light weight service locator container
 * not supported autowiring
 * @author yuuki.takezawa<yuuki.takezawa@comnect.jp.net>
 */
class FactoryContainer implements ContainerInterface
{
  protected Map<string, Scope> $scopes = Map{ };

  protected Set<string> $modules = Set{ };

  protected Map<string, (function(FactoryContainer): mixed)> $bindings = Map{ };

  protected array<string, array<string, (function(FactoryContainer): mixed)>> $parameters = [];

  /**
   * supported closure only
   */
  public function set(string $id, (function(FactoryContainer): mixed) $callback, Scope $scope = Scope::PROTOTYPE): void
  {
    $this->bindings->add(Pair{$id, $callback});
    $this->scopes->add(Pair{$id, $scope});
  }

  public function parameters(string $id, string $name, (function(FactoryContainer): mixed) $callback): void
  {
    $this->parameters[$id][$name] = $callback;
  }

  /**
   * Finds an entry of the container by its identifier and returns it.
   *
   * @param string $id Identifier of the entry to look for.
   *
   * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
   * @throws ContainerExceptionInterface Error while retrieving the entry.
   *
   * @return mixed Entry.
   */
  public function get($id): mixed
  {
    if($this->has($id)) {
      $resolved = $this->bindings->get($id);
      if (!is_null($resolved)) {
        if ($this->scopes->get($id) === Scope::SINGLETON) {
          return $this->shared($id);
        }
        return call_user_func($resolved, $this);
      }
    }

    try {
      $arguments = [];
      $reflectionClass = new \ReflectionClass($id);
      if ($reflectionClass->isInstantiable()) {
        $constructor = $reflectionClass->getConstructor();
        if ($constructor instanceof \ReflectionMethod) {
          $resolvedParameters = $this->resolveConstructorParameters($id, $constructor);
          if ($resolvedParameters instanceof \Generator) {
            $arguments = iterator_to_array($resolvedParameters);
          }
        }
        return $reflectionClass->newInstanceArgs($arguments);
      }
    } catch(\ReflectionException $e) {
      throw new NotFoundException(sprintf('Identifier "%s" is not binding.', $id));
    }
          throw new ContainerException(sprintf('Error retrieving "%s"', $id));
  }

  <<__Memoize>>
  protected function shared(string $id): mixed
  {
    return call_user_func($this->bindings->at($id), $this);
  }

  /**
   * Returns true if the container can return an entry for the given identifier.
   * Returns false otherwise.
   *
   * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
   * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
   *
   * @param string $id Identifier of the entry to look for.
   *
   * @return bool
   */
  public function has($id): bool
  {
    return $this->bindings->containsKey($id);
  }

  public function bindings(): Map<string, (function(FactoryContainer): mixed)>
  {
    return $this->bindings;
  }

  public function flush(): void
  {
    $this->bindings->clear();
    $this->scopes->clear();
  }

  public function remove(string $id): void
  {
    $this->bindings->removeKey($id);
    $this->scopes->removeKey($id);
  }

  public function register(string $moduleClassName): void
  {
    $this->modules->add($moduleClassName);
  }

  public function lockModule(): void
  {
    foreach ($this->modules->getIterator() as $iterator) {
      (new $iterator())->provide($this);
    }
  }

  protected function resolveConstructorParameters(string $id, \ReflectionMethod $constructor): \Generator
  {
    if ($parameters = $constructor->getParameters()) {
      foreach ($parameters as $parameter) {
        if (isset($this->parameters[$id])){
          if (isset($this->parameters[$id][$parameter->getName()])) {
            yield call_user_func($this->parameters[$id][$parameter->getName()], $this);
          }
        }
      }
    }
  }
}
