<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Integration\Validator\ValidationFailedExceptionMatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsClassLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsAutoloadingCompilerPass
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\Autoload\MatchConditionConstantsAutoloadingClassDiscovery
 *
 * @internal
 */
final class ValidationFailedExceptionMatchConditionCompilerConstantAutoloadingUnitTest extends TestCase
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

        if (!$this->container->hasDefinition(MatchConditionCompiler::class.'<'.ValidationFailedException::class.'>')) {
            self::markTestSkipped('ValidationFailedExceptionMatchConditionCompiler is not registered');
        }

        /** @var list<class-string> $classNames */
        $classNames = $this->container->getDefinition(ConstantsClassLoader::class)
            ->getArgument(0);

        self::assertContains(ValidationFailedExceptionMatchConditionCompiler::class, $classNames);
    }
}
