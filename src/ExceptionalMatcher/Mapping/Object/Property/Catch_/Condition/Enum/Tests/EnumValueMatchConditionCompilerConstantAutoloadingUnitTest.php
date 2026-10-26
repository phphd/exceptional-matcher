<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\EnumValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsClassLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsAutoloadingCompilerPass
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\Autoload\MatchConditionConstantsAutoloadingClassDiscovery
 *
 * @internal
 */
final class EnumValueMatchConditionCompilerConstantAutoloadingUnitTest extends TestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $this->container = $container;
    }

    public function testMatchConditionCompilerIsRecordedForTheConstantsAutoloading(): void
    {
        $this->container->compile();

        /** @var list<class-string> $classNames */
        $classNames = $this->container->getDefinition(ConstantsClassLoader::class)
            ->getArgument(0);

        self::assertContains(EnumValueMatchConditionCompiler::class, $classNames);
    }
}
