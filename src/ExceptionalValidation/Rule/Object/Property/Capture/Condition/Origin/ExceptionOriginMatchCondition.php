<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\Origin;

use LogicException;
use PhPhD\ExceptionalValidation\Rule\Object\Property\Capture\Condition\MatchCondition;
use Throwable;
use Webmozart\Assert\Assert;

use function function_exists;

/**
 * @internal
 *
 * @implements MatchCondition<Throwable>
 */
final readonly class ExceptionOriginMatchCondition implements MatchCondition
{
    public function __construct(
        /** @var ?class-string */
        private ?string $originClassName = null,
        private ?string $originFunctionName = null,
    ) {
        if (null !== $this->originClassName && null !== $this->originFunctionName) {
            Assert::methodExists($this->originClassName, $this->originFunctionName);
        } elseif (null !== $this->originClassName) {
            Assert::classExists($this->originClassName);
        } elseif (null !== $this->originFunctionName) {
            Assert::true(function_exists($this->originFunctionName));
        } else {
            throw new LogicException('At least one of the originClassName or originFunctionName must be set.');
        }
    }

    public function matches(Throwable $exception): bool
    {
        foreach ($exception->getTrace() as $traceItem) {
            if (isset($this->originClassName) && $this->originClassName !== ($traceItem['class'] ?? null)) {
                continue;
            }

            if (isset($this->originFunctionName) && $this->originFunctionName !== ($traceItem['function'] ?? null)) { // @phpstan-ignore nullCoalesce.offset
                continue;
            }

            return true;
        }

        return false;
    }
}
