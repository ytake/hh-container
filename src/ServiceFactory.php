<?hh // strict

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

use function is_null;
use function sprintf;
use function array_key_exists;

class ServiceFactory {

  protected dict<string, FactoryInterface> $factories = dict[];

  public function __construct(
    protected FactoryContainer $container
  ) {}
  
  <<__Rx, __Mutable>>
  public function registerFactory(FactoryInterface $factory): void {
    $this->factories[$factory->name()] = $factory;
  }
  
  <<__Rx, __Mutable>>
  public function has(string $factoryName): bool {
    return array_key_exists($factoryName, $this->factories);
  }

  public function create(string $factoryName): FactoryInterface::T {
    if ($this->has($factoryName)) {
      $resolve = $this->factories[$factoryName];
      if (!is_null($resolve)) {
        if ($resolve->scope() === Scope::SINGLETON) {
          return $this->createShared($factoryName);
        }
        return $resolve->provide($this->container);
      }
    }
    throw new NotFoundException(
      sprintf('"%s" is not found.', $factoryName),
    );
  }

  <<__Memoize>>
  protected function createShared(string $factoryName): FactoryInterface::T {
    return $this->factories[$factoryName]
      ->provide($this->container);
  }
}
