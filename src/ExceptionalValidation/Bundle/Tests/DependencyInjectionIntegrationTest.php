<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Bundle\Tests;

use PhPhD\ExceptionalValidation\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\CompositeRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\IterableOfObjectsRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\ObjectRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\ObjectRulesAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\PropertyRuleSetAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyCaptureRulesAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyNestedValidIterableRulesAssembler;
use PhPhD\ExceptionalValidation\Assembler\Object\Rules\Property\Rules\PropertyNestedValidObjectRuleAssembler;
use PhPhD\ExceptionalValidation\ConditionFactory\CaptureMatchConditionFactory;
use PhPhD\ExceptionalValidation\ConditionFactory\ValueExceptionMatchConditionFactory;
use PhPhD\ExceptionalValidation\Formatter\DefaultExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\DelegatingExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\ExceptionListViolationFormatter;
use PhPhD\ExceptionalValidation\Formatter\ViolationListExceptionFormatter;
use PhPhD\ExceptionalValidation\Handler\DefaultExceptionHandler;
use PhPhD\ExceptionalValidation\Handler\ExceptionHandler;
use PhPhD\ExceptionalValidation\Middleware\Messenger\ExceptionalValidationMiddleware;
use PhPhD\ExceptionalValidation\Model\Condition\ValueExceptionMatchCondition;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\CustomExceptionViolationFormatter;
use PhPhD\ExceptionToolkit\Unwrapper\Messenger\MessengerExceptionUnwrapper;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\VarExporter\LazyObjectInterface;

use function krsort;

/**
 * @covers \PhPhD\ExceptionalValidation\Bundle\PhdExceptionalValidationBundle
 * @covers \PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension
 *
 * @internal
 */
final class DependencyInjectionIntegrationTest extends BundleTestCase
{
    public function testServiceDefinitions(): void
    {
        $this->checkMiddleware();

        $this->checkExceptionHandler();

        $this->checkRuleSetAssembler();

        $this->checkExceptionUnwrapper();

        $this->checkConditionFactory();

        $this->checkViolationsListFormatter();

        $this->checkViolationFormatter();

        $this->checkObjectRuleSetAssembler();

        $this->checkObjectRulesAssembler();

        $this->checkPropertyRuleSetAssembler();

        $this->checkPropertyRulesAssemblers();
    }

    private function checkMiddleware(): void
    {
        $middleware = self::getContainer()->get('phd_exceptional_validation');

        self::assertInstanceOf(ExceptionalValidationMiddleware::class, $middleware);
    }

    private function checkExceptionHandler(): void
    {
        $exceptionHandler = self::getContainer()->get('phd_exceptional_validation.exception_handler');
        self::assertInstanceOf(ExceptionHandler::class, $exceptionHandler);
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionHandler);
        self::assertInstanceOf(DefaultExceptionHandler::class, $exceptionHandler->initializeLazyObject());
    }

    private function checkRuleSetAssembler(): void
    {
        $ruleSetAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler');
        self::assertInstanceOf(ObjectRuleSetAssembler::class, $ruleSetAssembler);
    }

    private function checkExceptionUnwrapper(): void
    {
        $exceptionUnwrapper = self::getContainer()->get('phd_exceptional_validation.exception_unwrapper');
        self::assertInstanceOf(LazyObjectInterface::class, $exceptionUnwrapper);
        self::assertFalse($exceptionUnwrapper->isLazyObjectInitialized());
        self::assertInstanceOf(MessengerExceptionUnwrapper::class, $exceptionUnwrapper->initializeLazyObject());
    }

    private function checkConditionFactory(): void
    {
        $matchConditionFactory = self::getContainer()->get('phd_exceptional_validation.match_condition_factory');
        self::assertInstanceOf(CaptureMatchConditionFactory::class, $matchConditionFactory);

        $conditionFactoryRegistry = $this->getConditionFactoryRegistry($matchConditionFactory);
        self::assertInstanceOf(ServiceLocator::class, $conditionFactoryRegistry);

        $providedServices = $conditionFactoryRegistry->getProvidedServices();
        krsort($providedServices);

        self::assertSame([
            ValueExceptionMatchCondition::class => ValueExceptionMatchConditionFactory::class,
        ], $providedServices);
    }

    private function checkViolationsListFormatter(): void
    {
        $violationsListFormatter = self::getContainer()->get('phd_exceptional_validation.violations_list_formatter');
        self::assertInstanceOf(ExceptionListViolationFormatter::class, $violationsListFormatter);
        self::assertInstanceOf(LazyObjectInterface::class, $violationsListFormatter);
    }

    private function checkViolationFormatter(): void
    {
        $violationFormatter = self::getContainer()->get('phd_exceptional_validation.violation_formatter');
        self::assertInstanceOf(DelegatingExceptionViolationFormatter::class, $violationFormatter);

        $defaultFormatter = self::getContainer()->get('phd_exceptional_validation.violation_formatter.default');
        self::assertInstanceOf(DefaultExceptionViolationFormatter::class, $defaultFormatter);

        $violationListExceptionFormatter = self::getContainer()->get('phd_exceptional_validation.violation_formatter.violation_list_exception');
        self::assertInstanceOf(ViolationListExceptionFormatter::class, $violationListExceptionFormatter);

        $formatterRegistry = $this->getFormatterRegistry($violationFormatter);
        self::assertInstanceOf(ServiceLocator::class, $formatterRegistry);

        $providedServices = $formatterRegistry->getProvidedServices();
        krsort($providedServices);
        self::assertSame([
            'default' => DefaultExceptionViolationFormatter::class,
            CustomExceptionViolationFormatter::class => CustomExceptionViolationFormatter::class,
            ViolationListExceptionFormatter::class => ViolationListExceptionFormatter::class,
        ], $providedServices);

        self::assertSame($defaultFormatter, $formatterRegistry->get('default'));
    }

    private function checkObjectRuleSetAssembler(): void
    {
        $objectRuleSetAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.object');
        self::assertInstanceOf(ObjectRuleSetAssembler::class, $objectRuleSetAssembler);
    }

    private function checkObjectRulesAssembler(): void
    {
        $objectRulesAssembler = self::getContainer()->get('phd_exceptional_validation.rule_set_assembler.object.rules');
        self::assertInstanceOf(ObjectRulesAssembler::class, $objectRulesAssembler);
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

    private function getFormatterRegistry(DelegatingExceptionViolationFormatter $violationFormatter): ?ContainerInterface
    {
        /** @psalm-suppress InaccessibleProperty */
        return (static fn (): ContainerInterface => $violationFormatter->formatterRegistry) // @phpstan-ignore-line
            ->bindTo(null, DelegatingExceptionViolationFormatter::class)?->__invoke()
        ;
    }

    private function getConditionFactoryRegistry(CaptureMatchConditionFactory $matchConditionFactory): ?ContainerInterface
    {
        /** @psalm-suppress InaccessibleProperty */
        return (static fn (): ContainerInterface => $matchConditionFactory->conditionFactoryRegistry) // @phpstan-ignore-line
            ->bindTo(null, CaptureMatchConditionFactory::class)?->__invoke()
        ;
    }
}
