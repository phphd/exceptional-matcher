<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\RegisterCustomViolationFormatterCompilerPass;
use PhPhD\ExceptionalMatcher\Bundle\Tests\PublicServiceCompilerPass;
use PhPhD\ExceptionalMatcher\Mapping\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Severity\DefectSeverity;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\AbstractTryMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\ChildOfPrivateCatchMessage;
use PhPhD\ExceptionalMatcher\Mapping\Linter\Tests\Stub\Invalid\UndefinedConstantConditionMessage;
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
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Class\ClassMappingLinter
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Class\MappingDefectCollector
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\Location\DefectLocation
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Report\Defect\MappingDefect
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Linter\Report\LintReport
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

        $container->addCompilerPass(new RegisterCustomViolationFormatterCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, RegisterCustomViolationFormatterCompilerPass::PRIORITY);

        $container->addCompilerPass(new PublicServiceCompilerPass(MappingLinter::class.'<class-string,'.LintReport::class.'>'));

        $container->compile();

        /** @var MappingLinter<class-string,LintReport> $linter */
        $linter = $container->get(MappingLinter::class.'<class-string,'.LintReport::class.'>');
        $this->linter = $linter;
    }

    public function testTryAttributePresentButCatchAttributesMissingIsReported(): void
    {
        [$defect] = $this->linter->lint([TryWithNoCatchAttributesMessage::class])->getDefects();

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertStringContainsString('#[Try_]', $defect->getMessage());
        self::assertStringContainsString('never matches anything', $defect->getMessage());
        self::assertSame(TryWithNoCatchAttributesMessage::class, $defect->getLocation()->getClassName());
        self::assertNull($defect->getLocation()->getPropertyName());
    }

    public function testCatchAttributesPresentButTryAttributeMissingIsReported(): void
    {
        [$defect] = $this->linter->lint([MessageWithNoTryAttribute::class])->getDefects();

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertStringContainsString('not marked with #[Try_]', $defect->getMessage());
        self::assertSame(MessageWithNoTryAttribute::class, $defect->getLocation()->getClassName());
        self::assertSame('property', $defect->getLocation()->getPropertyName());
    }

    public function testReportsAbstractTryClass(): void
    {
        [$defect] = $this->linter->lint([AbstractTryMessage::class])->getDefects();

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertStringContainsString('abstract', $defect->getMessage());
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

    public function testValidNestedOnlyMappingProducesNoWarning(): void
    {
        $defects = $this->linter->lint([RootObject::class])->getDefects();

        self::assertSame([], $defects);
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

    public function testBrokenMatchConditionMappingIsReported(): void
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
        self::assertInstanceOf(CatchExceptionMappingPlanCompilationFailedException::class, $cause);
        self::assertInstanceOf(CatchAttributeInstantiationFailedException::class, $cause->getPrevious());
    }
}
