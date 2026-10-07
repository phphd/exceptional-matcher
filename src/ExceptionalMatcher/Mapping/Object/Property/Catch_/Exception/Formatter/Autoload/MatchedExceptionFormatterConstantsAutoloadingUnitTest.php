<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Autoload;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded\ViolationsEmbeddedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsClassLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsAutoloadingCompilerPass
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\Autoload\MatchedExceptionFormatterConstantsAutoloadingClassDiscovery
 *
 * @internal
 */
final class MatchedExceptionFormatterConstantsAutoloadingUnitTest extends TestCase
{
    public function testMatchedExceptionFormattersAreHandedToTheConstantsClassLoader(): void
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

        self::assertContains(ViolationsEmbeddedExceptionFormatter::class, $classNames);
    }
}
