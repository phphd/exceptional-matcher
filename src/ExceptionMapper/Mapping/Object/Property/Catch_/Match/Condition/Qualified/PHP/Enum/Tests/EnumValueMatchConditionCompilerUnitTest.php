<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\PHP\Enum\Tests;

use InvalidArgumentException;
use LogicException;
use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\ExceptionMappingCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Exception\Tests\Trait\AssertPropertyCompilationFailedOn;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\PHP\Enum\EnumValueMatchConditionCompiler;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\PHP\Enum\Tests\Stub\Issue\DeliveryIssue;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\PHP\Enum\Tests\Stub\Method\DeliveryMethod;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\PHP\Enum\Tests\Stub\ReportDeliveryIssueCommand;
use PhPhD\ExceptionMapper\Tests\Trait\CatchException;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function sprintf;
use function var_export;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\PHP\Enum\EnumValueMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Type\PropertyType
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Type\Specification\IntType
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\PropertyExceptionMappingCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException
 * @covers \PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\ExceptionMappingPrintCompilationFailedException::getRootException
 *
 * @internal
 */
final class EnumValueMatchConditionCompilerUnitTest extends TestCase
{
    use CatchException;
    use AssertPropertyCompilationFailedOn;

    /** @var ExceptionMappingCompiler<ReflectionProperty,PropertyExceptionMappingBlueprint> */
    private ExceptionMappingCompiler $compiler;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->addCompilerPass(new PublicServiceCompilerPass(ExceptionMappingCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingBlueprint::class.'>'));

        $container->compile();

        /** @var ExceptionMappingCompiler<ReflectionProperty,PropertyExceptionMappingBlueprint> $compiler */
        $compiler = $container->get(ExceptionMappingCompiler::class.'<'.ReflectionProperty::class.','.PropertyExceptionMappingBlueprint::class.'>');
        $this->compiler = $compiler;
    }

    public function testThrowsWhenMatchConditionUsesNonValueErrorExceptionClass(): void
    {
        $property = new ReflectionProperty(ReportDeliveryIssueCommand::class, 'returnMethod');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(EnumValueMatchConditionCompiler::EXCEPTION_TYPE_GUARANTEE_MESSAGE);

        $compilationException = self::catchException(
            fn() => $this->compile($property),
            PropertyExceptionMappingPrintCompilationFailedException::class,
        );

        self::assertPropertyCompilationFailedOn($property, $compilationException);

        throw $compilationException->getRootException();
    }

    public function testThrowsWhenFromClauseIsMissing(): void
    {
        $property = new ReflectionProperty(ReportDeliveryIssueCommand::class, 'deliveryPriority');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: NULL');

        $compilationException = self::catchException(
            fn() => $this->compile($property),
            PropertyExceptionMappingPrintCompilationFailedException::class,
        );

        self::assertPropertyCompilationFailedOn($property, $compilationException);

        throw $compilationException->getRootException();
    }

    public function testThrowsWhenFromClassIsNotBackedEnum(): void
    {
        $property = new ReflectionProperty(ReportDeliveryIssueCommand::class, 'issue');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf(
            'EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: %s',
            var_export(DeliveryIssue::class, return: true),
        ));

        $compilationException = self::catchException(
            fn() => $this->compile($property),
            PropertyExceptionMappingPrintCompilationFailedException::class,
        );

        self::assertPropertyCompilationFailedOn($property, $compilationException);

        throw $compilationException->getRootException();
    }

    public function testThrowsWhenFromMethodIsNotEnumFrom(): void
    {
        $property = new ReflectionProperty(ReportDeliveryIssueCommand::class, 'deliveryMethod');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("EnumValueMatchCondition must specify `from: [DeliveryMethod::class, 'from']`, got: `from: [DeliveryMethod::class, 'tryFrom']`.");

        $compilationException = self::catchException(
            fn() => $this->compile($property),
            PropertyExceptionMappingPrintCompilationFailedException::class,
        );

        self::assertPropertyCompilationFailedOn($property, $compilationException);

        throw $compilationException->getRootException();
    }

    public function testThrowsWhenPropertyTypeDoesNotIncludeIntOrString(): void
    {
        $property = new ReflectionProperty(ReportDeliveryIssueCommand::class, 'reshipmentMethod');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(sprintf(EnumValueMatchConditionCompiler::PROPERTY_TYPE_GUARANTEE_MESSAGE, DeliveryMethod::class));

        $compilationException = self::catchException(
            fn() => $this->compile($property),
            PropertyExceptionMappingPrintCompilationFailedException::class,
        );

        self::assertPropertyCompilationFailedOn($property, $compilationException);

        throw $compilationException->getRootException();
    }

    public function testCompilesWhenPropertyTypeIncludesIntOrString(): void
    {
        $property = new ReflectionProperty(ReportDeliveryIssueCommand::class, 'reshipmentPriority');

        $this->compile($property);
    }

    private function compile(ReflectionProperty $property): void
    {
        $blueprint = $this->compiler->compile($property);

        self::assertNotNull($blueprint);

        $this->compiler->forceFullCompilation($blueprint);
    }
}
