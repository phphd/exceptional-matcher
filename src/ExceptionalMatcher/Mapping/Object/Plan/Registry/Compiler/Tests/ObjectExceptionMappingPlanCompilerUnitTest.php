<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\TryWithNoCatchAttributesMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Tests\Stub\SpyLogger;
use PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\ExceptionMappingPlanCompiler;
use PHPUnit\Framework\TestCase;
use Psr\Log\LogLevel;
use ReflectionClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\ObjectExceptionMappingPlanCompiler
 *
 * @internal
 */
final class ObjectExceptionMappingPlanCompilerUnitTest extends TestCase
{
    public function testErrorReporterIsNotSetUpRegardlessOfDebugModeWhenLoggerIsNotAvailable(): void
    {
        $container = $this->getContainer();
        $container->compile();

        /** @var ExceptionMappingPlanCompiler<ReflectionClass<object>,ObjectExceptionMappingPlan<object>> $compiler */
        $compiler = $container->get(ExceptionMappingPlanCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingPlan::class.'>');

        $this->expectException(ObjectExceptionMappingPlanCompilationFailedException::class);

        $compiler->compilePlan(new ReflectionClass(TryWithNoCatchAttributesMessage::class));
    }

    public function testLoggerIsUsedAsErrorReporterIsInNoDebugMode(): void
    {
        $container = $this->getContainer();

        $spyLogger = new SpyLogger();
        $container->set('logger', $spyLogger);
        $container->compile();

        /** @var ExceptionMappingPlanCompiler<ReflectionClass<object>,ObjectExceptionMappingPlan<object>> $compiler */
        $compiler = $container->get(ExceptionMappingPlanCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingPlan::class.'>');

        $plan = $compiler->compilePlan(new ReflectionClass(TryWithNoCatchAttributesMessage::class));
        self::assertNull($plan);

        $records = $spyLogger->flush();
        self::assertCount(1, $records);

        [$errorRecord] = $records;

        self::assertSame(LogLevel::ERROR, $errorRecord['level']);
        self::assertSame(
            'Class '.TryWithNoCatchAttributesMessage::class.' exception mapping compilation has failed.',
            $errorRecord['message'],
        );
        self::assertInstanceOf(
            ObjectExceptionMappingPlanCompilationFailedException::class,
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

        $container->addCompilerPass(new PublicServiceCompilerPass(ExceptionMappingPlanCompiler::class.'<'.ReflectionClass::class.','.ObjectExceptionMappingPlan::class.'>'));

        return $container;
    }
}
