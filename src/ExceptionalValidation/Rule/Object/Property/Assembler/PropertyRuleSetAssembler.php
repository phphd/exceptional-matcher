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

    /** @param CaptureRuleSetAssemblerService<PropertyRulesAssembler> $captureListAssemblerService */
    public function assemble(CaptureRule $parentRule, CaptureRuleSetAssemblerService $captureListAssemblerService): ?CaptureRule
    {
        /** @var object $object */
        $object = $parentRule->getValue();

        $captureRuleSet = new LazyRuleSet(
            /** @param LazyRuleSet<CaptureRule> $lazyCaptureRuleSet */
            function (LazyRuleSet $lazyCaptureRuleSet) use ($parentRule, $object, $captureListAssemblerService): ?CaptureRule {
                $propertyRuleSet = new PropertyRuleSet(
                    $parentRule,
                    $this->getName(),
                    $this->getValue($object),
                    $lazyCaptureRuleSet,
                );

                return $captureListAssemblerService->assemble($propertyRuleSet, new PropertyRulesAssembler($this->reflectionProperty));
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
