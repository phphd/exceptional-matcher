<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Compiler\Tests;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\Log\SpyLogger;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\TryWithNoCatchAttributesMessage;
use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Compiler\Exception\ObjectExceptionMappingPrintCompilationFailedException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Compiler\ObjectExceptionMappingCompiler
 *
 * @internal
 */
final class ObjectExceptionMappingCompilerUnitTest extends TestCase
{
    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
            'kernel.debug' => false,
        ]);

        $container->addCompilerPass(new PublicServiceCompilerPass(ExceptionMappingCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingBlueprint::class.'>'));

        $this->container = $container;
    }

    public function testErrorReporterIsNotSetUpWhenLoggerIsNotAvailableRegardlessOfDebugMode(): void
    {
        $this->container->compile();

        /** @var ExceptionMappingCompiler<ReflectionClass<object>,ObjectExceptionMappingBlueprint<object>> $compiler */
        $compiler = $this->container->get(ExceptionMappingCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingBlueprint::class.'>');

        $this->expectException(ObjectExceptionMappingPrintCompilationFailedException::class);

        $compiler->compile(new ReflectionClass(TryWithNoCatchAttributesMessage::class));
    }

    public function testLoggerIsUsedAsErrorReporterIsInNoDebugMode(): void
    {
        $spyLogger = new SpyLogger();
        $this->container->set('logger', $spyLogger);
        $this->container->compile();

        /** @var ExceptionMappingCompiler<ReflectionClass<object>,ObjectExceptionMappingBlueprint<object>> $compiler */
        $compiler = $this->container->get(ExceptionMappingCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingBlueprint::class.'>');

        $print = $compiler->compile(new ReflectionClass(TryWithNoCatchAttributesMessage::class));
        self::assertNull($print);

        $records = $spyLogger->flush();
        self::assertCount(1, $records);

        [$errorRecord] = $records;

        self::assertSame(LogLevel::ERROR, $errorRecord['level']);
        self::assertSame(
            'Class exception mapping compilation has failed.',
            $errorRecord['message'],
        );
        self::assertInstanceOf(
            ObjectExceptionMappingPrintCompilationFailedException::class,
            $errorRecord['context']['exception'],
        );
    }
}
