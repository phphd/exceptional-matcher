<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidationBundle\Tests;

use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\CompositeRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\ObjectRulesAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\PropertyRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyCaptureRulesAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyNestedValidObjectRuleAssembler;
use PhPhD\ExceptionalValidation\Formatter\ExceptionViolationsListFormatter;
use PhPhD\ExceptionalValidation\Handler\ExceptionalHandler;
use PhPhD\ExceptionalValidation\Handler\ExceptionHandler;
use PhPhD\ExceptionalValidationBundle\Messenger\ExceptionalValidationMiddleware;
use Symfony\Component\VarExporter\LazyObjectInterface;

/**
 * @covers \PhPhD\ExceptionalValidationBundle\PhdExceptionalValidationBundle
 * @covers \PhPhD\ExceptionalValidationBundle\DependencyInjection\PhdExceptionalValidationExtension
 *
 * @internal
 */
final class DependencyInjectionTest extends TestCase
{
    public function testServiceDefinitions(): void
    {
        $container = self::getContainer();

        $middleware = $container->get('phd_exceptional_validation');

        self::assertInstanceOf(ExceptionalValidationMiddleware::class, $middleware);

        $exceptionHandler = $container->get('phd_exceptional_validation.exception_handler');
        self::assertInstanceOf(ExceptionHandler::class, $exceptionHandler);
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionHandler);
        self::assertInstanceOf(ExceptionalHandler::class, $exceptionHandler->initializeLazyObject());

        $ruleSetAssembler = $container->get('phd_exceptional_validation.rule_set_assembler');
        self::assertInstanceOf(ObjectRuleSetAssembler::class, $ruleSetAssembler);

        $violationsListFormatter = $container->get('phd_exceptional_validation.violations_list_formatter');
        self::assertInstanceOf(ExceptionViolationsListFormatter::class, $violationsListFormatter);
        self::assertInstanceOf(LazyObjectInterface::class, $violationsListFormatter);

        $objectRuleSetAssembler = $container->get('phd_exceptional_validation.rule_set_assembler.object');
        self::assertInstanceOf(ObjectRuleSetAssembler::class, $objectRuleSetAssembler);

        $objectRulesAssembler = $container->get('phd_exceptional_validation.rule_set_assembler.object.rules');
        self::assertInstanceOf(ObjectRulesAssembler::class, $objectRulesAssembler);

        $propertyRuleSetAssembler = $container->get('phd_exceptional_validation.rule_set_assembler.property');
        self::assertInstanceOf(PropertyRuleSetAssembler::class, $propertyRuleSetAssembler);

        $propertyRulesAssembler = $container->get('phd_exceptional_validation.rule_set_assembler.property.rules');
        self::assertInstanceOf(CompositeRuleSetAssembler::class, $propertyRulesAssembler);

        $propertyCaptureRulesAssembler = $container->get('phd_exceptional_validation.rule_set_assembler.property.rules.captures');
        self::assertInstanceOf(PropertyCaptureRulesAssembler::class, $propertyCaptureRulesAssembler);

        $propertyNestedValidObjectRuleAssembler = $container->get('phd_exceptional_validation.rule_set_assembler.property.rules.nested_valid_object');
        self::assertInstanceOf(CaptureRuleSetAssembler::class, $propertyNestedValidObjectRuleAssembler);
        self::assertInstanceOf(LazyObjectInterface::class, $propertyNestedValidObjectRuleAssembler);
        self::assertInstanceOf(PropertyNestedValidObjectRuleAssembler::class, $propertyNestedValidObjectRuleAssembler->initializeLazyObject());
    }
}
