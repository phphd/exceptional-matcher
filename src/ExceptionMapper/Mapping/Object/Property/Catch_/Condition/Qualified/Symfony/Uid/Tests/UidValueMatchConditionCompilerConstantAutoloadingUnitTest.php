<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\Tests;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\ConstantsClassLoader;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid\UidValueMatchConditionCompiler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\ConstantsAutoloadingCompilerPass
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\Autoload\QualifiedMatchConditionClassDiscovery
 *
 * @internal
 */
final class UidValueMatchConditionCompilerConstantAutoloadingUnitTest extends TestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $this->container = $container;
    }

    public function testMatchConditionCompilerIsRecordedForTheConstantsAutoloading(): void
    {
        $this->container->compile();

        if (!$this->container->hasDefinition(MatchConditionCompiler::class.'<'.InvalidUidException::class.'>')) {
            self::markTestSkipped('UidValueMatchConditionCompiler is not registered');
        }

        /** @var list<class-string> $classNames */
        $classNames = $this->container->getDefinition(ConstantsClassLoader::class)
            ->getArgument(0)
        ;

        self::assertContains(UidValueMatchConditionCompiler::class, $classNames);
    }
}
