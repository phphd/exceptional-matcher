<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\ObjectRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssemblerService;
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
        $this->checkRuleSetAssembler();

        $this->checkObjectRuleSetAssembler();

        $this->checkPropertyRuleSetAssembler();

        $this->checkPropertyRulesAssemblers();
    }

    private function checkRuleSetAssembler(): void
    {
        $ruleSetAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler');
        self::assertInstanceOf(ObjectRuleSetAssemblerService::class, $ruleSetAssembler);
    }

    private function checkObjectRuleSetAssembler(): void
    {
        $objectRuleSetAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.object');
        self::assertInstanceOf(ObjectRuleSetAssemblerService::class, $objectRuleSetAssembler);
    }

    private function checkPropertyRuleSetAssembler(): void
    {
        $propertyRuleSetAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.property');
        self::assertInstanceOf(PropertyRuleSetAssemblerService::class, $propertyRuleSetAssembler);
    }

    private function checkPropertyRulesAssemblers(): void
    {
        $propertyRulesAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.property.rules');
        self::assertInstanceOf(CaptureRuleSetAssemblerService::class, $propertyRulesAssembler);

        if (PhdExceptionalValidationExtension::nativeProxiesAreSupported()) {
            self::assertInstanceOf(CompositeRuleSetAssemblerService::class, $propertyRulesAssembler);
        } else {
            self::assertInstanceOf(LazyObjectInterface::class, $propertyRulesAssembler);
            self::assertInstanceOf(CompositeRuleSetAssemblerService::class, $propertyRulesAssembler->initializeLazyObject());
        }

        $propertyCaptureRulesAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.property.rules.captures');
        self::assertInstanceOf(PropertyCaptureRulesAssemblerService::class, $propertyCaptureRulesAssembler);

        $propertyNestedValidObjectRuleAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_object');
        self::assertInstanceOf(PropertyNestedValidObjectRuleAssemblerService::class, $propertyNestedValidObjectRuleAssembler);

        $propertyNestedValidIterableRuleAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_iterable');
        self::assertInstanceOf(PropertyNestedValidIterableRulesAssemblerService::class, $propertyNestedValidIterableRuleAssembler);
    }
}
