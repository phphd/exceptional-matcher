<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule;

use Closure;
use PhPhD\ExceptionalValidation\Rule\Exception\ExceptionPackage;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Path\PropertyPath;
use RuntimeException;

/**
 * @internal
 *
 * @template T of CaptureRule
 */
final class LazyRuleSet implements CaptureRule
{
    /** @var ?T */
    private ?CaptureRule $innerRule = null;

    /** @var ?Closure(LazyRuleSet<T>): ?T */
    private ?Closure $ruleSetFactory;

    /** @param Closure(LazyRuleSet<T>): ?T $ruleSetFactory */
    public function __construct(
        Closure $ruleSetFactory,
    ) {
        $this->ruleSetFactory = $ruleSetFactory;
    }

    public function process(ExceptionPackage $package): bool
    {
        return $this->innerRule()->process($package);
    }

    public function getParent(): ?CaptureRule
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
    public function build(): ?CaptureRule
    {
        if (null === $this->ruleSetFactory) {
            return $this->innerRule;
        }

        $this->innerRule = ($this->ruleSetFactory)($this);
        $this->ruleSetFactory = null;

        return $this->innerRule;
    }

    /** @return T */
    private function innerRule(): CaptureRule
    {
        return $this->innerRule ?? $this->build() ?? throw new RuntimeException('Lazy rule set is not initialized.');
    }
}
