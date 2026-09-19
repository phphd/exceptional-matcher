<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\Autoload;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Value\ExceptionValueMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsClassLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsAutoloadingCompilerPass
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\Autoload\MatchConditionConstantsAutoloadingClassDiscovery
 *
 * @internal
 */
final class MatchConditionConstantsAutoloadingUnitTest extends TestCase
{
    public function testMatchConditionCompilersAreHandedToTheConstantsClassLoader(): void
    {
        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var list<class-string> $classNames */
        $classNames = $container->getDefinition(ConstantsClassLoader::class)
            ->getArgument(0)
        ;

        self::assertContains(ExceptionValueMatchConditionCompiler::class, $classNames);
    }
}
