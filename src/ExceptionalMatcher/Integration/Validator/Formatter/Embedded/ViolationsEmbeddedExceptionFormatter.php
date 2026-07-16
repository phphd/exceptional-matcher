<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded;

use Closure;
use LogicException;
use PhPhD\ExceptionalMatcher\Exception\MatchedException;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\ExceptionViolationFormatter;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validation;
use Throwable;
use Webmozart\Assert\Assert;

use function array_map;
use function iterator_to_array;

/** @api */
const embedded_violations = ViolationsEmbeddedExceptionFormatter::class;

/**
 * @internal - use {@see embedded_violations} constant for a class reference instead
 *
 * @implements ExceptionViolationFormatter<ViolationsEmbeddedException|ValidationFailedException>
 */
final class ViolationsEmbeddedExceptionFormatter implements ExceptionViolationFormatter
{
    /** @api */
    public function __construct(
        /** @var ?Closure(string,array<array-key,mixed>):string */
        private readonly ?Closure $translate = null,
    ) {
    }

    /**
     * @param MatchedException<ViolationsEmbeddedException|ValidationFailedException> $matchedException
     *
     * @return non-empty-list<ConstraintViolation>
     */
    public function format(MatchedException $matchedException): array
    {
        $exception = $matchedException->getException();
        Assert::isInstanceOfAny($exception, [ViolationsEmbeddedException::class, ValidationFailedException::class]);

        /** @var list<ConstraintViolationInterface> $violationList */
        $violationList = iterator_to_array($exception->getViolations());

        if ([] === $violationList) {
            throw new LogicException('Violation list must not be empty');
        }

        $rule = $matchedException->getRule();

        $root = $rule->getRootObject();
        $propertyPath = $rule->getPropertyPath()
            ->join('.')
        ;
        $redoTranslation = $this->shouldRedoTranslation($exception);

        /** @psalm-suppress PossiblyNullFunctionCall */
        return array_map(
            fn (ConstraintViolationInterface $violation): ConstraintViolation => new ConstraintViolation(
                $redoTranslation
                    ? ($this->translate)($violation->getMessageTemplate(), $violation->getParameters())
                    : $violation->getMessage(),
                $violation->getMessageTemplate(),
                $violation->getParameters(),
                $root,
                $propertyPath,
                $violation->getInvalidValue(),
                $violation->getPlural(),
                $violation->getCode(),
                $violation->getConstraint(),
                $violation->getCause(),
            ),
            $violationList,
        );
    }

    /**
     * Validation::createCallable() instantiates a validator afresh,
     * so the pre-translated message does not reflect an Accept-Language header,
     * thereby it requires re-translation.
     *
     * @phpstan-assert-if-true !null $this->translate
     */
    private function shouldRedoTranslation(Throwable $exception): bool
    {
        if (null === $this->translate) {
            return false;
        }

        if (ValidationFailedException::class !== $exception::class) {
            return false;
        }

        return Validation::class === ($exception->getTrace()[0]['class'] ?? null);
    }
}
