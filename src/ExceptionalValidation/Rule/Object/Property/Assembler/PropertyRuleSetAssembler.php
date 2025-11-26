<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler;

use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssembler;
use PhPhD\ExceptionalValidation\Rule\Assembler\CaptureRuleSetAssemblerService;
use PhPhD\ExceptionalValidation\Rule\CaptureRule;
use PhPhD\ExceptionalValidation\Rule\LazyRuleSet;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Assembler\Rules\PropertyRulesAssembler;
use PhPhD\ExceptionalValidation\Rule\Object\Property\PropertyRuleSet;
use ReflectionProperty;

/** @internal */
final readonly class PropertyRuleSetAssembler implements CaptureRuleSetAssembler
{
    public function __construct(
        private ReflectionProperty $reflectionProperty,
    ) {
    }

    /** @param CaptureRuleSetAssemblerService<PropertyRulesAssembler> $captureListAssembler */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssemblerService $captureListAssembler): ?CaptureRule
    {
        /** @var object $object */
        $object = $parentRule->getValue();

        $captureRuleSet = new LazyRuleSet(
            /** @param LazyRuleSet<CaptureRule> $lazyCaptureRuleSet */
            function (LazyRuleSet $lazyCaptureRuleSet) use ($parentRule, $object, $captureListAssembler): ?CaptureRule {
                $propertyRuleSet = new PropertyRuleSet(
                    $parentRule,
                    $this->getName(),
                    $this->getValue($object),
                    $lazyCaptureRuleSet,
                );

                $propertyRulesEnvelope = new PropertyRulesAssembler($this->reflectionProperty);

                return $captureListAssembler->assemble($propertyRuleSet, $propertyRulesEnvelope);
            },
        );

        return $captureRuleSet->build()?->getParent();
    }

    public function getName(): string
    {
        return $this->reflectionProperty->getName();
    }

    public function getValue(object $message): mixed
    {
        if (!$this->reflectionProperty->isInitialized($message)) {
            return null;
        }

        return $this->reflectionProperty->getValue($message);
    }
}
