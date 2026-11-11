<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\Tests;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\ConstantsClassLoader;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\Tests\Stub\SpyClassLoader;
use PHPUnit\Framework\TestCase;

use function spl_autoload_register;
use function spl_autoload_unregister;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\ConstantsClassLoader
 */
final class ConstantsClassLoaderUnitTest extends TestCase
{
    private SpyClassLoader $spyClassLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->spyClassLoader = new SpyClassLoader();

        spl_autoload_register($this->spyClassLoader);
    }

    protected function tearDown(): void
    {
        spl_autoload_unregister($this->spyClassLoader);

        parent::tearDown();
    }

    public function testEachClassNameIsHandedToTheAutoloader(): void
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        $load = new ConstantsClassLoader(['Elijah', 'John', 'David']); // @phpstan-ignore argument.type (non-existent names)

        $load();

        self::assertSame(['Elijah', 'John', 'David'], $this->spyClassLoader->flushRequestedClassNames());

        // second load
        $load();

        self::assertSame([], $this->spyClassLoader->flushRequestedClassNames());
    }
}
