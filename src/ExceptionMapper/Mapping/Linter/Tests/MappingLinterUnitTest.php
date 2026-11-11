<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Linter\Tests;

use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionMapper\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionMapper\Mapping\Linter\Report\Defect\Severity\DefectSeverity;
use PhPhD\ExceptionMapper\Mapping\Linter\Report\LintReport;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\AbstractTryMessage;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\ChildOfPrivateCatchMessage;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\Invalid\LazilyDiscoveredUnknownMatchConditionMessage;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\Invalid\MissingEnumFromConditionMessage;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\Invalid\UndefinedConstantConditionMessage;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\ParentPrivateCatchMessage;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\TryWithNoCatchAttributesMessage;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\TryWithNonMatchableObjectMessage;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\UnregisteredFormatter;
use PhPhD\ExceptionMapper\Mapping\Linter\Tests\Stub\UnregisteredFormatterMessage;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchExceptionMappingPrintCompilationFailedException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Iterable\Tests\Stub\RootObject;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\MessageWithNoTryAttribute;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\NestedHandleableMessage;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\NestedItem;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Class\ClassMappingLinter
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Class\CompilationDefectCollector
 * @covers \PhPhD\ExceptionMapper\Mapping\Blueprint\Compiler\Log\SpyLogger
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Report\LintReport
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Report\Class\ClassReport
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Report\Defect\MappingDefect
 * @covers \PhPhD\ExceptionMapper\Mapping\Linter\Report\Defect\Location\DefectLocation
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Blueprint\ObjectExceptionMappingBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Compiler\ObjectExceptionMappingCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Blueprint\Registry\Compiler\Exception\ObjectExceptionMappingPrintCompilationFailedException
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\PropertyExceptionMappingBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\PropertyExceptionMappingCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Blueprint\Compiler\Exception\PropertyExceptionMappingPrintCompilationFailedException
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\CatchExceptionMappingBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\CatchExceptionMappingCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchAttributeInstantiationFailedException
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Exception\CatchExceptionMappingPrintCompilationFailedException
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\QualifiedExceptionMatchFormatter
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\UnregisteredExceptionFormatterException
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\MemoizingIteratorAggregate
 */
final class MappingLinterUnitTest extends TestCase
{
    /** @var MappingLinter<class-string, LintReport> */
    private MappingLinter $linter;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->addCompilerPass(new PublicServiceCompilerPass(MappingLinter::class.'<class-string,'.LintReport::class.'>'));

        $container->compile();

        /** @var MappingLinter<class-string,LintReport> $linter */
        $linter = $container->get(MappingLinter::class.'<class-string,'.LintReport::class.'>');
        $this->linter = $linter;
    }

    public function testTryAttributePresentButCatchAttributesMissingIsReported(): void
    {
        [$classReport] = $this->linter->lint([TryWithNoCatchAttributesMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertSame(TryWithNoCatchAttributesMessage::class, $defect->getLocation()->toReference());
        self::assertSame([
            '#[Try_] class does not define any #[Catch_] mappings and no nested matchable properties, so it never matches anything.',
            'Class exception mapping compilation has failed.',
        ], $defect->getMessages());
    }

    public function testCatchAttributesPresentButTryAttributeMissingIsReported(): void
    {
        [$classReport] = $this->linter->lint([MessageWithNoTryAttribute::class])->getClassReports();

        [$defect] = $classReport->getDefects();
        self::assertCount(1, $classReport->getDefects());

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertSame(MessageWithNoTryAttribute::class.'::$property', $defect->getLocation()->toReference());
        self::assertSame([
            'Properties declare #[Catch_] mappings, but the class is not marked with #[Try_], so it never matches anything.',
        ], $defect->getMessages());
    }

    public function testReportsAbstractTryClass(): void
    {
        [$classReport] = $this->linter->lint([AbstractTryMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertSame([
            '#[Try_] on an abstract class has no effect: the attribute is not inherited by the subclasses.',
            'Class exception mapping compilation has failed.',
        ], $defect->getMessages());
    }

    public function testReportsTryClassWhoseNestedObjectCannotCarryABlueprint(): void
    {
        [$classReport] = $this->linter->lint([TryWithNonMatchableObjectMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertSame(TryWithNonMatchableObjectMessage::class, $defect->getLocation()->toReference());
        self::assertSame([
            '#[Try_] class does not define any #[Catch_] mappings and no nested matchable properties, so it never matches anything.',
            'Class exception mapping compilation has failed.',
        ], $defect->getMessages());
    }

    public function testReportsParentPrivateCatchProperties(): void
    {
        [$classReport] = $this->linter->lint([ChildOfPrivateCatchMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertSame(ChildOfPrivateCatchMessage::class.'::$parentDivisor', $defect->getLocation()->toReference());
        self::assertSame([
            'Private property '.ParentPrivateCatchMessage::class.'::$parentDivisor declares #[Catch_] mappings that are invisible to '.ChildOfPrivateCatchMessage::class.'.',
        ], $defect->getMessages());
    }

    public function testReportsUnregisteredFormatter(): void
    {
        [$classReport] = $this->linter->lint([UnregisteredFormatterMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertSame(UnregisteredFormatterMessage::class.'::$divisor', $defect->getLocation()->toReference());
        self::assertSame([
            'Exception Formatter is not registered: "'.UnregisteredFormatter::class.'"',
            '#[Catch_] exception mapping compilation failed.',
        ], $defect->getMessages());
    }

    public function testReportsBrokenMatchConditionMapping(): void
    {
        [$classReport] = $this->linter->lint([MissingEnumFromConditionMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertSame(MissingEnumFromConditionMessage::class.'::$deliveryMethod', $defect->getLocation()->toReference());
        self::assertSame([
            'EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: NULL',
            '#[Catch_] exception mapping compilation failed.',
        ], $defect->getMessages());
    }

    public function testReportsUndefinedMatchConstant(): void
    {
        [$classReport] = $this->linter->lint([UndefinedConstantConditionMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertSame(UndefinedConstantConditionMessage::class.'::$divisor', $defect->getLocation()->toReference());

        $cause = $defect->getCause();
        self::assertInstanceOf(CatchExceptionMappingPrintCompilationFailedException::class, $cause);
        self::assertInstanceOf(CatchAttributeInstantiationFailedException::class, $cause->getPrevious());

        self::assertSame([
            'Undefined constant "PhPhD\\ExceptionMapper\\Mapping\\Object\\Property\\Catch_\\Condition\\Undefined\\undefined_condition"',
            '#[Catch_] attribute instantiation failed.',
            '#[Catch_] exception mapping compilation failed.',
        ], $defect->getMessages());
    }

    /** @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Qualified\QualifiedMatchConditionCompiler */
    public function testReportsLazilyCompiledBrokenMatchCondition(): void
    {
        [$classReport] = $this->linter->lint([LazilyDiscoveredUnknownMatchConditionMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertSame(LazilyDiscoveredUnknownMatchConditionMessage::class.'::$divisor', $defect->getLocation()->toReference());
        self::assertInstanceOf(CatchExceptionMappingPrintCompilationFailedException::class, $defect->getCause());
        self::assertSame([
            'Match condition compiler not found for: unknown_condition',
            '#[Catch_] exception mapping compilation failed.',
        ], $defect->getMessages());
    }

    public function testNoWarningIsReportedOnAValidNestedOnlyMapping(): void
    {
        $defects = $this->linter->lint([RootObject::class])->getClassReports();

        self::assertSame([], $defects);
    }

    public function testNoErrorsAreReportedOnValidMappings(): void
    {
        $report = $this->linter->lint([
            HandleableMessageStub::class,
            NestedHandleableMessage::class,
            NestedItem::class,
        ]);

        self::assertFalse($report->hasDefects());
        self::assertSame([], $report->getClassReports());
    }
}
