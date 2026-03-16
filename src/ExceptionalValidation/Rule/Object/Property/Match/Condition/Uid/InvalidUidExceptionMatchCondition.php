<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\Uid;

use PhPhD\ExceptionalValidation\Rule\Object\Property\Match\Condition\MatchCondition;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Throwable;

/**
 * @api
 *
 * @implements MatchCondition<InvalidUidException>
 */
final class InvalidUidExceptionMatchCondition implements MatchCondition
{
    public function __construct(
        private readonly string $value,
    ) {
    }

    /** @param InvalidUidException $exception */
    public function matches(Throwable $exception): bool
    {
        return $exception->invalidValue === $this->value;
    }
}
