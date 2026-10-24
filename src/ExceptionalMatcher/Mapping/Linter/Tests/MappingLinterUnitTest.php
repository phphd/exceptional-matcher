<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Tests;

use LogicException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity\DefectSeverity;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\AbstractTryMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\ChildOfPrivateCatchMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\Invalid\LazilyDiscoveredUnknownMatchConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\Invalid\UndefinedConstantConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\ParentPrivateCatchMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\TryWithNoCatchAttributesMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\TryWithNonMatchableObjectMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\UnregisteredFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\UnregisteredFormatterMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests\Stub\Invalid\MissingEnumFromConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Exception\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Iterable\Tests\Stub\RootObject;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\MessageWithNoTryAttribute;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NestedHandleableMessage;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NestedItem;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Class\ClassMappingLinter
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Class\CompilationDefectCollector
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Plan\Compiler\Log\SpyLogger
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Class\ClassReport
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Plan\ObjectExceptionMappingPlan
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\ObjectExceptionMappingPlanCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Plan\Registry\Compiler\Exception\ObjectExceptionMappingPlanCompilationFailedException
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\PropertyExceptionMappingPlan
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\Compiler\PropertyExceptionMappingPlanCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\Compiler\Exception\PropertyExceptionMappingPlanCompilationFailedException
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\CatchExceptionMappingPlan
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\CatchExceptionMappingPlanCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Exception\CatchExceptionMappingPlanCompilationFailedException
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionPlan
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\ReusableIteratorAggregate
 */
final class MappingLinterUnitTest extends TestCase
{
    /** @var MappingLinter<class-string, LintReport> */
    private MappingLinter $linter;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
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
            'Class '.TryWithNoCatchAttributesMessage::class.' exception mapping compilation has failed.',
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
             'Class '.AbstractTryMessage::class.' exception mapping compilation has failed.'
        ], $defect->getMessages());
    }

    public function testReportsTryClassWhoseNestedObjectCannotCarryAPlan(): void
    {
        [$classReport] = $this->linter->lint([TryWithNonMatchableObjectMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertSame(TryWithNonMatchableObjectMessage::class, $defect->getLocation()->toReference());
        self::assertSame([
            '#[Try_] class does not define any #[Catch_] mappings and no nested matchable properties, so it never matches anything.',
            'Class '.TryWithNonMatchableObjectMessage::class.' exception mapping compilation has failed.',
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
            'Exception Formatter "'.UnregisteredFormatter::class.'" is not registered in the formatter registry.',
            'CatchExceptionMappingPlan compilation failed.',
        ], $defect->getMessages());
    }

    public function testBrokenMatchConditionMappingIsReported(): void
    {
        [$classReport] = $this->linter->lint([MissingEnumFromConditionMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertSame(MissingEnumFromConditionMessage::class.'::$weekDay', $defect->getLocation()->toReference());
        self::assertSame([
            'EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: NULL',
            'CatchExceptionMappingPlan compilation failed.',
        ], $defect->getMessages());
    }

    public function testReportsUndefinedMatchConstant(): void
    {
        [$classReport] = $this->linter->lint([UndefinedConstantConditionMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertSame(UndefinedConstantConditionMessage::class.'::$divisor', $defect->getLocation()->toReference());

        $cause = $defect->getCause();
        self::assertInstanceOf(CatchExceptionMappingPlanCompilationFailedException::class, $cause);
        self::assertInstanceOf(CatchAttributeInstantiationFailedException::class, $cause->getPrevious());

        self::assertSame([
            'Undefined constant "PhPhD\\ExceptionalMatcher\\Mapping\\Object\\Property\\Catch_\\Condition\\Undefined\\undefined_condition"',
            '#[Catch_] attribute instantiation failed.',
            'CatchExceptionMappingPlan compilation failed.',
        ], $defect->getMessages());
    }

    public function testLazilyCompiledBrokenMatchConditionIsReported(): void
    {
        [$classReport] = $this->linter->lint([LazilyDiscoveredUnknownMatchConditionMessage::class])->getClassReports();

        [$defect] = $classReport->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertSame(LazilyDiscoveredUnknownMatchConditionMessage::class.'::$divisor', $defect->getLocation()->toReference());
        self::assertInstanceOf(LogicException::class, $defect->getCause());
        self::assertSame([
            'Match condition compiler not found for: unknown_condition',
        ], $defect->getMessages());
    }

    public function testValidNestedOnlyMappingProducesNoWarning(): void
    {
        $defects = $this->linter->lint([RootObject::class])->getClassReports();

        self::assertSame([], $defects);
    }

    public function testValidMappingsProduceNoErrors(): void
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
