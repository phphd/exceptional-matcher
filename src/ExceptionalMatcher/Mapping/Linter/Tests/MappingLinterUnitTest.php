<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\TestServicesCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity\DefectSeverity;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\AbstractTryMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\ChildOfPrivateCatchMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\Invalid\UndefinedConstantConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\TryWithNonMatchableObjectMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\UnmatchableTryMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\UnregisteredFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\UnregisteredFormatterMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Enum\Tests\Stub\Invalid\MissingEnumFromConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Exception\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Exception\CatchExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Iterable\Tests\Stub\RootObject;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Plan\Compiler\Exception\PropertyExceptionMappingPlanCompilationFailedException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NestedHandleableMessage;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NestedItem;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\MessageWithNoTryAttribute;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

use function array_filter;
use function array_values;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation
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

        $container->addCompilerPass(new TestServicesCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, TestServicesCompilerPass::PRIORITY);

        $container->compile();

        /** @var MappingLinter $linter */
        $linter = $container->get(MappingLinter::class.'<class-string,'.LintReport::class.'>');
        $this->linter = $linter;
    }

    public function testReportsCatchPropertiesWithoutTryAttribute(): void
    {
        [$defect] = $this->linter->lint([MessageWithNoTryAttribute::class])->getDefects();

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertStringContainsString('not marked with #[Try_]', $defect->getMessage());
        self::assertSame(MessageWithNoTryAttribute::class, $defect->getLocation()->getClassName());
        self::assertSame('property', $defect->getLocation()->getPropertyName());
    }

    public function testValidMappingsProduceNoErrors(): void
    {
        $report = $this->linter->lint([
            HandleableMessageStub::class,
            NestedHandleableMessage::class,
            NestedItem::class,
        ]);

        self::assertFalse($report->hasDefects());
        self::assertSame([], $report->getDefects());
    }

    public function testReportsAbstractTryClass(): void
    {
        [$defect] = $this->linter->lint([AbstractTryMessage::class])->getDefects();

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertStringContainsString('abstract', $defect->getMessage());
    }

    public function testValidNestedOnlyMappingProducesNoWarning(): void
    {
        $defects = $this->linter->lint([RootObject::class])->getDefects();

        self::assertSame([], $defects);
    }

    public function testReportsTryClassWithoutMappingPlan(): void
    {
        [$defect] = $this->linter->lint([UnmatchableTryMessage::class])->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertStringContainsString('#[Try_]', $defect->getMessage());
        self::assertStringContainsString('never matches anything', $defect->getMessage());
        self::assertSame(UnmatchableTryMessage::class, $defect->getLocation()->getClassName());
        self::assertNull($defect->getLocation()->getPropertyName());
    }

    public function testReportsTryClassWhoseNestedObjectCannotCarryAPlan(): void
    {
        [$defect] = $this->linter->lint([TryWithNonMatchableObjectMessage::class])->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertStringContainsString('#[Try_]', $defect->getMessage());
        self::assertStringContainsString('never matches anything', $defect->getMessage());
        self::assertSame(TryWithNonMatchableObjectMessage::class, $defect->getLocation()->getClassName());
        self::assertNull($defect->getLocation()->getPropertyName());
    }

    public function testReportsParentPrivateCatchProperties(): void
    {
        [$defect] = $this->linter->lint([ChildOfPrivateCatchMessage::class])->getDefects();

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertStringContainsString('$parentCaughtValue', $defect->getMessage());
        self::assertStringContainsString('invisible', $defect->getMessage());
        self::assertSame('parentCaughtValue', $defect->getLocation()->getPropertyName());
    }

    public function testReportsUnregisteredFormatter(): void
    {
        [$defect] = $this->linter->lint([UnregisteredFormatterMessage::class])->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertStringContainsString(UnregisteredFormatter::class, $defect->getMessage());
        self::assertSame('caughtValue', $defect->getLocation()->getPropertyName());
    }

    public function testReportsBrokenCatchMappingWithVerbatimMessage(): void
    {
        [$defect] = $this->linter->lint([MissingEnumFromConditionMessage::class])->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertStringContainsString(
            'EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: NULL',
            $defect->getMessage(),
        );

        self::assertSame('weekDay', $defect->getLocation()->getPropertyName());
    }

    public function testReportsUndefinedMatchConstant(): void
    {
        [$defect] = $this->linter->lint([UndefinedConstantConditionMessage::class])->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertStringContainsString('Undefined constant', $defect->getMessage());
        self::assertStringContainsString('undefined_condition', $defect->getMessage());
        self::assertSame('caughtValue', $defect->getLocation()->getPropertyName());

        $cause = $defect->getCause();
        self::assertInstanceOf(PropertyExceptionMappingPlanCompilationFailedException::class, $cause);

        $catchFailure = $cause->getPrevious();
        self::assertInstanceOf(CatchExceptionMappingPlanCompilationFailedException::class, $catchFailure);
        self::assertInstanceOf(CatchAttributeInstantiationFailedException::class, $catchFailure->getPrevious());
    }
}
