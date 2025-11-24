<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Class;

use LogicException;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Throwable;

use function is_a;

/**
 * @internal
 *
 * @implements MatchCondition<Throwable>
 */
final readonly class ExceptionClassMatchCondition implements MatchCondition
{
    public function __construct(
        /** @var class-string<Throwable> */
        private string $exceptionClass,
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
