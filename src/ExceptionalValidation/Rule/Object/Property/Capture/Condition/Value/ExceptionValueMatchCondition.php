<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Value;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Throwable;

/**
 * @api
 *
 * @implements MatchCondition<ValueException>
 */
final class ExceptionValueMatchCondition implements MatchCondition
{
    public function __construct(
        private readonly mixed $value,
    ) {
    }

    /** @param ValueException $exception */
    public function matches(Throwable $exception): bool
    {
        return $exception->getValue() === $this->value;
    }
}
