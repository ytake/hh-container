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

class ServiceFactory {

  protected Map<string, FactoryInterface> $factories = Map{};

  public function __construct(
    protected FactoryContainer $container
  ) {}

  public function registerFactory(FactoryInterface $factory): void {
    $this->factories->add(Pair{$factory->name(), $factory});
  }

  public function resolve(string $factoryName): FactoryInterface::T {
    $resolve = $this->factories->get($factoryName);
    if (!\is_null($resolve)) {
      return $resolve->provide($this->container);
    }
    throw new NotFoundException(
      \sprintf('"%s" is not found.', $factoryName),
    );
  }
}
