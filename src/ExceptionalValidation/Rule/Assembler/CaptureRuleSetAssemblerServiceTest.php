<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Assembler;

use PhPhD\ExceptionalValidation\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\IterableOfObjectsRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Assembler\Rules\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssembler;
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
        self::assertInstanceOf(ObjectRuleSetAssembler::class, $ruleSetAssembler);
    }

    private function checkObjectRuleSetAssembler(): void
    {
        $objectRuleSetAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.object');
        self::assertInstanceOf(ObjectRuleSetAssembler::class, $objectRuleSetAssembler);
    }

    private function checkPropertyRuleSetAssembler(): void
    {
        $propertyRuleSetAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.property');
        self::assertInstanceOf(PropertyRuleSetAssembler::class, $propertyRuleSetAssembler);
    }

    private function checkPropertyRulesAssemblers(): void
    {
        $propertyRulesAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.property.rules');
        self::assertInstanceOf(CompositeRuleSetAssembler::class, $propertyRulesAssembler);

        $propertyCaptureRulesAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.property.rules.captures');
        self::assertInstanceOf(PropertyCaptureRulesAssembler::class, $propertyCaptureRulesAssembler);

        $propertyNestedValidObjectRuleAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_object');
        self::assertInstanceOf(CaptureRuleSetAssembler::class, $propertyNestedValidObjectRuleAssembler);
        self::assertInstanceOf(LazyObjectInterface::class, $propertyNestedValidObjectRuleAssembler);
        self::assertInstanceOf(PropertyNestedValidObjectRuleAssembler::class, $propertyNestedValidObjectRuleAssembler->initializeLazyObject());

        $propertyNestedValidIterableRuleAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_iterable');
        self::assertInstanceOf(CaptureRuleSetAssembler::class, $propertyNestedValidIterableRuleAssembler);
        self::assertInstanceOf(LazyObjectInterface::class, $propertyNestedValidIterableRuleAssembler);
        self::assertInstanceOf(PropertyNestedValidIterableRulesAssembler::class, $propertyNestedValidIterableRuleAssembler->initializeLazyObject());

        $iterableOfObjectsRuleSetAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.iterable_of_objects');
        self::assertInstanceOf(IterableOfObjectsRuleSetAssembler::class, $iterableOfObjectsRuleSetAssembler);
    }
}
