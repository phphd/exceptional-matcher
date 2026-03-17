<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Assembler;

use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\PropertyMatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler\PropertyMatchingRulesAssemblerService;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @covers \PhPhD\ExceptionalMatcher\Bundle\PhdExceptionalValidationBundle
 * @covers \PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalValidationExtension
 *
 * @internal
 */
final class MatchingRuleSetAssemblerServiceTest extends BundleTestCase
{
    public function testServiceDefinitions(): void
    {
        $this->checkObjectRuleSetAssembler();

        $this->checkPropertyRuleSetAssembler();

        $this->checkPropertyRulesAssemblers();
    }

    private function checkObjectRuleSetAssembler(): void
    {
        $objectRuleSetAssembler = self::getContainer()->get(MatchingRuleSetAssemblerService::class.'<'.ObjectMatchingRuleSetAssembler::class.'>');
        self::assertInstanceOf(ObjectMatchingRuleSetAssemblerService::class, $objectRuleSetAssembler);
    }

    private function checkPropertyRuleSetAssembler(): void
    {
        $propertyRuleSetAssembler = self::getContainer()->get(MatchingRuleSetAssemblerService::class.'<'.PropertyMatchingRuleSetAssembler::class.'>');
        self::assertInstanceOf(PropertyMatchingRuleSetAssemblerService::class, $propertyRuleSetAssembler);
    }

    private function checkPropertyRulesAssemblers(): void
    {
        $propertyRulesAssembler = self::getContainer()->get(MatchingRuleSetAssemblerService::class.'<'.PropertyMatchingRulesAssembler::class.'>');
        self::assertInstanceOf(MatchingRuleSetAssemblerService::class, $propertyRulesAssembler);

        if (PhdExceptionalValidationExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(CompositeRuleSetAssemblerService::class, $propertyRulesAssembler);
            $compositeInnerAssemblers = $this->getCompositeInnerAssemblers($propertyRulesAssembler);
        } else {
            self::assertInstanceOf(LazyObjectInterface::class, $propertyRulesAssembler);
            self::assertInstanceOf(CompositeRuleSetAssemblerService::class, $propertyRulesAssembler->initializeLazyObject());
            $compositeInnerAssemblers = null;
        }

        $propertyMatchingRulesAssembler = self::getContainer()->get(PropertyMatchingRulesAssemblerService::class);
        self::assertInstanceOf(PropertyMatchingRulesAssemblerService::class, $propertyMatchingRulesAssembler);

        $propertyNestedValidObjectRuleAssembler = self::getContainer()->get(PropertyNestedValidObjectRuleAssemblerService::class);
        self::assertInstanceOf(PropertyNestedValidObjectRuleAssemblerService::class, $propertyNestedValidObjectRuleAssembler);

        $propertyNestedValidIterableRuleAssembler = self::getContainer()->get(PropertyNestedValidIterableRulesAssemblerService::class);
        self::assertInstanceOf(PropertyNestedValidIterableRulesAssemblerService::class, $propertyNestedValidIterableRuleAssembler);

        if (null !== $compositeInnerAssemblers) {
            self::assertSame($compositeInnerAssemblers, [
                $propertyMatchingRulesAssembler,
                $propertyNestedValidObjectRuleAssembler,
                $propertyNestedValidIterableRuleAssembler,
            ]);
        }
    }

    /**
     * @template T of MatchingRuleSetAssembler
     *
     * @param CompositeRuleSetAssemblerService<T> $propertyRulesAssembler
     *
     * @return iterable<MatchingRuleSetAssemblerService<T>>
     */
    private function getCompositeInnerAssemblers(CompositeRuleSetAssemblerService $propertyRulesAssembler): iterable
    {
        /**
         * @var iterable<MatchingRuleSetAssemblerService<T>>
         *
         * @psalm-suppress UndefinedThisPropertyFetch
         */
        return (fn (): iterable => $this->assemblers)->call($propertyRulesAssembler);
    }
}
