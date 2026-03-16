<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule;

use Closure;
use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionReciprocal;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath;
use RuntimeException;

/**
 * @internal
 *
 * @template T of MatchingRule
 */
final class LazyMatchingRule implements MatchingRule
{
    /** @var ?T */
    private ?MatchingRule $innerRule = null;

    /** @var ?Closure(LazyMatchingRule<T>): ?T */
    private ?Closure $ruleSetFactory;

    /** @param Closure(LazyMatchingRule<T>): ?T $ruleFactory */
    public function __construct(
        Closure $ruleFactory,
    ) {
        $this->ruleSetFactory = $ruleFactory;
    }

    public function process(ExceptionReciprocal $reciprocal): bool
    {
        return $this->innerRule()->process($reciprocal);
    }

    public function getParent(): ?MatchingRule
    {
        return $this->innerRule()->getParent();
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->innerRule()->getPropertyPath();
    }

    public function getEnclosingObject(): object
    {
        return $this->innerRule()->getEnclosingObject();
    }

    public function getRootObject(): object
    {
        return $this->innerRule()->getRootObject();
    }

    public function getValue(): mixed
    {
        return $this->innerRule()->getValue();
    }

    /** @return ?T */
    public function build(): ?MatchingRule
    {
        if (null === $this->ruleSetFactory) {
            return $this->innerRule;
        }

        $this->innerRule = ($this->ruleSetFactory)($this);
        $this->ruleSetFactory = null;

        return $this->innerRule;
    }

    /** @return T */
    private function innerRule(): MatchingRule
    {
        return $this->innerRule ?? $this->build() ?? throw new RuntimeException('Lazy rule set is not initialized.');
    }
}
