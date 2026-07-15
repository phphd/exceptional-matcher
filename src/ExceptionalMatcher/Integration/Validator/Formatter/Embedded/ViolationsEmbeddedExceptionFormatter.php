<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\Embedded;

use LogicException;
use PhPhD\ExceptionalMatcher\Exception\MatchedException;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\ExceptionViolationFormatter;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
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
    /**
     * @param MatchedException<ViolationsEmbeddedException|ValidationFailedException> $matchedException
     *
     * @return non-empty-list<ConstraintViolation>
     */
    public function format(MatchedException $matchedException): array
    {
        $exception = $matchedException->getException();
        Assert::isInstanceOfAny($exception, [ViolationsEmbeddedException::class, ValidationFailedException::class]);

        $rule = $matchedException->getRule();
        $root = $rule->getRootObject();
        $propertyPath = $rule->getPropertyPath()
            ->join('.')
        ;
        /** @var list<ConstraintViolationInterface> $violationList */
        $violationList = iterator_to_array($exception->getViolations());

        if ([] === $violationList) {
            throw new LogicException('Violation list must not be empty');
        }

        return array_map(
            static fn (ConstraintViolationInterface $violation): ConstraintViolation => new ConstraintViolation(
                $violation->getMessage(),
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
}
