<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Assembler;

use ArrayIterator;
use Generator;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\CompositeMatchingRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\MatchExceptionRule;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\PropertyMatchingRuleSet;
use ReflectionProperty;
use Throwable;
use Webmozart\Assert\Assert;

/** @internal */
final class PropertyMatchingRulesAssembler implements MatchingRuleSetAssembler
{
    public function __construct(
        private readonly PropertyMatchingRuleSet $ownerRule,
        private readonly ReflectionProperty $reflectionProperty,
    ) {
    }

    public function assemble(PropertyMatchingRulesAssemblerService $service): ?CompositeMatchingRule
    {
        $rules = new ArrayIterator();
        $ruleSet = new CompositeMatchingRule($this->ownerRule, $rules);

        foreach ($this->getCatchAttributes() as $catchAttribute) {
            $conditionBlueprint = $service->matchConditionCompiler->compile($catchAttribute);

            Assert::notNull($conditionBlueprint);

            $rules->append(new MatchExceptionRule(
                $ruleSet,
                $conditionBlueprint->bind($this->ownerRule),
                $catchAttribute->getFormat(),
                $catchAttribute->getMessage(),
            ));
        }

        if (0 === $rules->count()) {
            return null;
        }

        return $ruleSet;
    }

    public function getOwnerRule(): PropertyMatchingRuleSet
    {
        return $this->ownerRule;
    }

    /** @return Generator<Catch_<Throwable,Throwable>> */
    private function getCatchAttributes(): Generator
    {
        $catchAttributes = $this->reflectionProperty->getAttributes(Catch_::class);

        foreach ($catchAttributes as $catchAttribute) {
            yield $catchAttribute->newInstance();
        }
    }
}
