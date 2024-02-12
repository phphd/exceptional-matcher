<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Model\Sets;

use PhPhD\ExceptionalValidation\Model\CaptureRule;
use PhPhD\ExceptionalValidation\Model\ValueObject\PropertyPath;
use Throwable;

use function array_merge;

final class CompositeRuleSet implements CaptureRule
{
    public function __construct(
        private readonly CaptureRule $parent,
        /** @var iterable<CaptureRule> $rules */
        private readonly iterable $rules,
    ) {
    }

    public function capture(Throwable $exception): array
    {
        $hits = [];

        foreach ($this->rules as $rule) {
            $hits[] = $rule->capture($exception);
        }

        return array_merge(...$hits);
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
