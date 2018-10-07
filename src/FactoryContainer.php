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

  protected Map<string, Map<Scope, TCallable>> $mapper = Map{};

  public function set(
    string $id,
    TCallable $callback,
    Scope $scope = Scope::PROTOTYPE,
  ): void {
    $this->mapper->add(Pair {$id, Map{$scope => $callback}});
  }

  public function get($id): mixed {
    if ($this->has($id)) {
      $resolved = $this->mapper[$id];
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
    throw new NotFoundException(
      sprintf('Identifier "%s" is not binding.', $id),
    );
  }

  <<__Memoize>>
  protected function shared(string $id): mixed {
    $call = $this->mapper[$id]->firstValue();
    if(!is_null($call)) {
      return call_user_func($call, $this);
    }
  }

  <<__Rx, __Mutable>>
  public function has($id): bool {
    return array_key_exists($id, $this->mapper);
  }

  <<__Rx>>
  public function bindings(
  ): ImmMap<string, Map<Scope, TCallable>> {
    return $this->mapper->toImmMap();
  }

  public function remove(string $id): void {
    if ($this->has($id)) {
      $this->mapper->removeKey($id);
    }
  }

  public function flush(): void {
    $this->mapper->clear();
  }

  public function registerModule(TServiceModule $moduleClassName): void {
    new $moduleClassName()
    |> $$->provide($this);
  }

  public function callable(MethodCallIntreface $invokable): mixed {
    return $invokable->proceed();
  }
}
