<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssemblerService;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @covers \PhPhD\ExceptionalValidation\Bundle\PhdExceptionalValidationBundle
 * @covers \PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension
 *
 * @internal
 */
final class CaptureRuleSetAssemblerServiceTest extends BundleTestCase
{
    public function testServiceDefinitions(): void
    {
        $this->checkObjectRuleSetAssembler();

        $this->checkPropertyRuleSetAssembler();

        $this->checkPropertyRulesAssemblers();
    }

    private function checkObjectRuleSetAssembler(): void
    {
        $objectRuleSetAssembler = self::getContainer()->get(CaptureRuleSetAssemblerService::class.'<'.ObjectRuleSetAssembler::class.'>');
        self::assertInstanceOf(ObjectRuleSetAssemblerService::class, $objectRuleSetAssembler);
    }

    private function checkPropertyRuleSetAssembler(): void
    {
        $propertyRuleSetAssembler = self::getContainer()->get(CaptureRuleSetAssemblerService::class.'<'.PropertyRuleSetAssembler::class.'>');
        self::assertInstanceOf(PropertyRuleSetAssemblerService::class, $propertyRuleSetAssembler);
    }

    private function checkPropertyRulesAssemblers(): void
    {
        $propertyRulesAssembler = self::getContainer()->get(CaptureRuleSetAssemblerService::class.'<'.PropertyCaptureRulesAssembler::class.'>');
        self::assertInstanceOf(CaptureRuleSetAssemblerService::class, $propertyRulesAssembler);

        if (PhdExceptionalValidationExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(CompositeRuleSetAssemblerService::class, $propertyRulesAssembler);
            $compositeInnerAssemblers = $this->getCompositeInnerAssemblers($propertyRulesAssembler);
        } else {
            self::assertInstanceOf(LazyObjectInterface::class, $propertyRulesAssembler);
            self::assertInstanceOf(CompositeRuleSetAssemblerService::class, $propertyRulesAssembler->initializeLazyObject());
            $compositeInnerAssemblers = null;
        }

        $propertyCaptureRulesAssembler = self::getContainer()->get(PropertyCaptureRulesAssemblerService::class);
        self::assertInstanceOf(PropertyCaptureRulesAssemblerService::class, $propertyCaptureRulesAssembler);

        $propertyNestedValidObjectRuleAssembler = self::getContainer()->get(PropertyNestedValidObjectRuleAssemblerService::class);
        self::assertInstanceOf(PropertyNestedValidObjectRuleAssemblerService::class, $propertyNestedValidObjectRuleAssembler);

        $propertyNestedValidIterableRuleAssembler = self::getContainer()->get(PropertyNestedValidIterableRulesAssemblerService::class);
        self::assertInstanceOf(PropertyNestedValidIterableRulesAssemblerService::class, $propertyNestedValidIterableRuleAssembler);

        if (null !== $compositeInnerAssemblers) {
            self::assertSame($compositeInnerAssemblers, [
                $propertyCaptureRulesAssembler,
                $propertyNestedValidObjectRuleAssembler,
                $propertyNestedValidIterableRuleAssembler,
            ]);
        }
    }

    /**
     * @template T of CaptureRuleSetAssembler
     *
     * @param CompositeRuleSetAssemblerService<T> $propertyRulesAssembler
     *
     * @return iterable<CaptureRuleSetAssemblerService<T>>
     */
    private function getCompositeInnerAssemblers(CompositeRuleSetAssemblerService $propertyRulesAssembler): iterable
    {
        /**
         * @var iterable<CaptureRuleSetAssemblerService<T>>
         *
         * @psalm-suppress UndefinedThisPropertyFetch
         */
        return (fn (): iterable => $this->assemblers)->call($propertyRulesAssembler);
    }
}
