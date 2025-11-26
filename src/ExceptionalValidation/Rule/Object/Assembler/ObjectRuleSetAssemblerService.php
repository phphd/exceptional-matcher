<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Assembler;

use ArrayIterator;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Assembler\CompositeRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\PropertyRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidIterableRulesAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyNestedValidObjectRuleAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Assembler\PropertyCaptureRulesAssemblerService;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Composite\CaptureMatchConditionFactory;

/**
 * @internal
 *
 * @implements CaptureRuleSetAssemblerService<ObjectRuleSetAssembler>
 */
final readonly class ObjectRuleSetAssemblerService implements CaptureRuleSetAssemblerService
{
    /** @param CaptureRuleSetAssemblerService<PropertyRuleSetAssembler> $propertyRuleSetAssembler */
    public function __construct(
        private CaptureRuleSetAssemblerService $propertyRuleSetAssembler,
    ) {
    }

    public static function create(): self
    {
        /** @var ArrayIterator<array-key,CaptureRuleSetAssemblerService<PropertyRulesAssembler>> $captureListAssemblers */
        $captureListAssemblers = new ArrayIterator();
        $propertyRulesAssembler = new CompositeRuleSetAssemblerService($captureListAssemblers);
        $propertyRuleSetAssembler = new PropertyRuleSetAssemblerService($propertyRulesAssembler);

        $objectRuleSetAssembler = new self($propertyRuleSetAssembler);

        $captureListAssemblers->append(new PropertyCaptureRulesAssemblerService(CaptureMatchConditionFactory::create()));
        $captureListAssemblers->append(new PropertyNestedValidObjectRuleAssemblerService($objectRuleSetAssembler));
        $captureListAssemblers->append(new PropertyNestedValidIterableRulesAssemblerService($objectRuleSetAssembler));

        return $objectRuleSetAssembler;
    }

    public function assembleForMessage(object $message, ?CaptureRule $parentRule = null): ?CaptureRule
    {
        $envelope = ObjectRuleSetAssembler::createForMessage($message);

        if (null === $envelope) {
            return null;
        }

        return $this->assemble($parentRule, $envelope);
    }

    /** @param ObjectRuleSetAssembler $assembler */
    public function assemble(?CaptureRule $parentRule, CaptureRuleSetAssembler $assembler): ?CaptureRule
    {
        return $assembler->assemble($parentRule, $this->propertyRuleSetAssembler);
    }
}
