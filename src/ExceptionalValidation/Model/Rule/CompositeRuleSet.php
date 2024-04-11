<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Rule;

use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use PhPhD\ExceptionalValidation\Model\ValueObject\ThrownExceptions;

use function array_merge;

/** @internal */
final class CompositeRuleSet implements CaptureRule
{
    public function __construct(
        private readonly CaptureRule $parent,
        /** @var iterable<CaptureRule> $rules */
        private readonly iterable $rules,
    ) {
    }

    public function capture(ThrownExceptions $thrownExceptions): array
    {
        $hits = [];

        foreach ($this->rules as $rule) {
            $hits[] = $rule->capture($thrownExceptions);
        }

        return array_merge(...$hits);
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath();
    }

    public function getEnclosingObject(): object
    {
        return $this->parent->getEnclosingObject();
    }

    public function getRoot(): object
    {
        return $this->parent->getRoot();
    }

    public function getValue(): mixed
    {
        return $this->parent->getValue();
    }
}
