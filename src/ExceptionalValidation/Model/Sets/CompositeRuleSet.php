<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Sets;

use PhPhD\ExceptionalValidation\Model\CaptureRule;
use PhPhD\ExceptionalValidation\Model\ValueObject\CaughtException;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use Throwable;

final class CompositeRuleSet implements CaptureRule
{
    public function __construct(
        private readonly CaptureRule $parent,
        /** @var iterable<CaptureRule> $rules */
        private readonly iterable $rules,
    ) {
    }

    public function capture(Throwable $exception): ?CaughtException
    {
        foreach ($this->rules as $rule) {
            if (null !== ($hit = $rule->capture($exception))) {
                return $hit;
            }
        }

        return null;
    }

    public function getPropertyPath(): PropertyPath
    {
        return $this->parent->getPropertyPath();
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
