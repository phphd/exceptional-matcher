<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Class;

use LogicException;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchCondition;
use Throwable;

use function is_a;

/**
 * @internal
 *
 * @implements MatchCondition<Throwable>
 */
final class ExceptionClassMatchCondition implements MatchCondition
{
    public function __construct(
        /** @var class-string<Throwable> */
        private readonly string $exceptionClass,
    ) {
        if (!is_a($this->exceptionClass, Throwable::class, true)) { // @phpstan-ignore function.alreadyNarrowedType
            throw new LogicException('Exception class condition should only be used for exception classes that implement Throwable');
        }
    }

    public function matches(Throwable $exception): bool
    {
        return $exception instanceof $this->exceptionClass;
    }
}
