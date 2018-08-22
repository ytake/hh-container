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

class ServiceFactory {

  protected Map<string, FactoryInterface> $factories = Map{};

  public function __construct(
    protected FactoryContainer $container
  ) {}

  public function registerFactory(FactoryInterface $factory): void {
    $this->factories->add(Pair{$factory->name(), $factory});
  }

  public function create(string $factoryName): FactoryInterface::T {
    $resolve = $this->factories->get($factoryName);
    if (!is_null($resolve)) {
      if ($resolve->scope() === Scope::Singleton) {
        return $this->createShared($factoryName);
      }
      return $resolve->provide($this->container);
    }
    throw new NotFoundException(
      sprintf('"%s" is not found.', $factoryName),
    );
  }

  <<__Memoize>>
  protected function createShared(string $factoryName): FactoryInterface::T {
    return $this->factories
      ->at($factoryName)
      ->provide($this->container);
  }
}
