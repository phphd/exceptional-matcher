<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Tests;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\DefectSeverity;
use PhPhD\ExceptionalMatcher\Integration\Linter\Defect\MappingDefect;
use PhPhD\ExceptionalMatcher\Integration\Linter\MappingLinter;
use PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub\AbstractTryMessage;
use PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub\ChildOfPrivateCatchMessage;
use PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub\Invalid\UndefinedConstantConditionMessage;
use PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub\TryWithNonMatchableObjectMessage;
use PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub\UnmatchableTryMessage;
use PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub\UnregisteredFormatter;
use PhPhD\ExceptionalMatcher\Integration\Linter\Tests\Stub\UnregisteredFormatterMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\_Plan\_Compiler\Exception\CatchAttributeInstantiationFailedException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Enum\Tests\Stub\Invalid\MissingEnumFromConditionMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Tests\Stub\RootObject;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NestedHandleableMessage;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NestedItem;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\NotHandleableMessageStub;
use PHPUnit\Framework\TestCase;

use function array_filter;
use function array_values;

/**
 * @internal
 *
 * @covers \PhPhD\ExceptionalMatcher\Integration\Linter\MappingLinter
 * @covers \PhPhD\ExceptionalMatcher\Integration\Linter\Defect\MappingDefect
 * @covers \PhPhD\ExceptionalMatcher\Integration\Linter\Defect\DefectLocation
 */
final class MappingLinterUnitTest extends TestCase
{
    private MappingLinter $linter;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var MappingLinter $linter */
        $linter = $container->get(MappingLinter::class);
        $this->linter = $linter;
    }

    public function testValidMappingsProduceNoErrors(): void
    {
        $defects = $this->linter->lint([
            HandleableMessageStub::class,
            NestedHandleableMessage::class,
            NestedItem::class,
        ]);

        self::assertSame([], $this->errorsOf($defects));
    }

    public function testReportsCatchPropertiesWithoutTryAttribute(): void
    {
        [$defect] = $this->linter->lint([NotHandleableMessageStub::class]);

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertStringContainsString('not marked with #[Try_]', $defect->getMessage());
        self::assertSame(NotHandleableMessageStub::class, $defect->getLocation()->getClassName());
        self::assertNull($defect->getLocation()->getPropertyName());
    }

    public function testReportsAbstractTryClass(): void
    {
        [$defect] = $this->linter->lint([AbstractTryMessage::class]);

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertStringContainsString('abstract', $defect->getMessage());
    }

    public function testValidNestedOnlyMappingProducesNoWarning(): void
    {
        $defects = $this->linter->lint([RootObject::class]);

        self::assertSame([], $defects);
    }

    public function testReportsTryClassWithoutMappingPlan(): void
    {
        [$defect] = $this->linter->lint([UnmatchableTryMessage::class]);

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertStringContainsString('#[Try_]', $defect->getMessage());
        self::assertStringContainsString('never matches anything', $defect->getMessage());
        self::assertSame(UnmatchableTryMessage::class, $defect->getLocation()->getClassName());
        self::assertNull($defect->getLocation()->getPropertyName());
    }

    public function testReportsTryClassWhoseNestedObjectCannotCarryAPlan(): void
    {
        [$defect] = $this->linter->lint([TryWithNonMatchableObjectMessage::class]);

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertStringContainsString('#[Try_]', $defect->getMessage());
        self::assertStringContainsString('never matches anything', $defect->getMessage());
        self::assertSame(TryWithNonMatchableObjectMessage::class, $defect->getLocation()->getClassName());
        self::assertNull($defect->getLocation()->getPropertyName());
    }

    public function testReportsParentPrivateCatchProperties(): void
    {
        [$defect] = $this->linter->lint([ChildOfPrivateCatchMessage::class]);

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertStringContainsString('$parentCaughtValue', $defect->getMessage());
        self::assertStringContainsString('invisible', $defect->getMessage());
        self::assertSame('parentCaughtValue', $defect->getLocation()->getPropertyName());
    }

    public function testReportsUnregisteredFormatter(): void
    {
        [$defect] = $this->linter->lint([UnregisteredFormatterMessage::class]);

        self::assertSame(DefectSeverity::Warning, $defect->getSeverity());
        self::assertStringContainsString(UnregisteredFormatter::class, $defect->getMessage());
        self::assertSame('caughtValue', $defect->getLocation()->getPropertyName());
    }

    public function testReportsBrokenCatchMappingWithVerbatimMessage(): void
    {
        [$defect] = $this->linter->lint([MissingEnumFromConditionMessage::class]);

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertStringContainsString(
            'EnumValueMatchCondition requires `from:` to contain a class-string of BackedEnum, got: NULL',
            $defect->getMessage(),
        );

        self::assertSame('weekDay', $defect->getLocation()->getPropertyName());
    }

    public function testReportsUndefinedMatchConstant(): void
    {
        [$defect] = $this->linter->lint([UndefinedConstantConditionMessage::class]);

        self::assertSame(DefectSeverity::Error, $defect->getSeverity());
        self::assertStringContainsString('Undefined constant', $defect->getMessage());
        self::assertStringContainsString('undefined_condition', $defect->getMessage());
        self::assertSame('caughtValue', $defect->getLocation()->getPropertyName());
        self::assertInstanceOf(CatchAttributeInstantiationFailedException::class, $defect->getCause());
    }

    /**
     * @param list<MappingDefect> $defects
     *
     * @return list<MappingDefect>
     */
    private function errorsOf(array $defects): array
    {
        return array_values(array_filter(
            $defects,
            static fn (MappingDefect $defect): bool => $defect->getSeverity()->is(DefectSeverity::Error),
        ));
    }
}
