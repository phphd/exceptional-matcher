<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Blueprint\Compiler\Log\SpyLogger;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\TryWithNoCatchAttributesMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\Exception\ObjectExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\ObjectExceptionMappingCompiler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Blueprint\Registry\Compiler\ObjectExceptionMappingCompiler
 *
 * @internal
 */
final class ObjectExceptionMappingCompilerUnitTest extends TestCase
{
    public function testErrorReporterIsNotSetUpWhenLoggerIsNotAvailableRegardlessOfDebugMode(): void
    {
        $container = $this->getContainer();
        $container->compile();

        /** @var ExceptionMappingCompiler<ReflectionClass<object>,ObjectExceptionMappingBlueprint<object>> $compiler */
        $compiler = $container->get(ExceptionMappingCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingBlueprint::class.'>');

        $this->expectException(ObjectExceptionMappingPrintCompilationFailedException::class);

        $compiler->compilePrint(new ReflectionClass(TryWithNoCatchAttributesMessage::class));
    }

    public function testLoggerIsUsedAsErrorReporterIsInNoDebugMode(): void
    {
        $container = $this->getContainer();

        $spyLogger = new SpyLogger();
        $container->set('logger', $spyLogger);
        $container->compile();

        /** @var ExceptionMappingCompiler<ReflectionClass<object>,ObjectExceptionMappingBlueprint<object>> $compiler */
        $compiler = $container->get(ExceptionMappingCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingBlueprint::class.'>');

        $print = $compiler->compilePrint(new ReflectionClass(TryWithNoCatchAttributesMessage::class));
        self::assertNull($print);

        $records = $spyLogger->flush();
        self::assertCount(1, $records);

        [$errorRecord] = $records;

        self::assertSame(LogLevel::ERROR, $errorRecord['level']);
        self::assertSame(
            'Class '.TryWithNoCatchAttributesMessage::class.' exception mapping compilation has failed.',
            $errorRecord['message'],
        );
        self::assertInstanceOf(
            ObjectExceptionMappingPrintCompilationFailedException::class,
            $errorRecord['context']['exception'],
        );
    }

    private function getContainer(): ContainerBuilder
    {
        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
            'kernel.debug' => false,
        ]);

        $container->addCompilerPass(new PublicServiceCompilerPass(ExceptionMappingCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingBlueprint::class.'>'));

        return $container;
    }
}
