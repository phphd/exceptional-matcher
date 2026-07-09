<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Validator\Formatter\ViolationList;

use LogicException;
use PhPhD\ExceptionalMatcher\Exception\MatchedException;
use PhPhD\ExceptionalMatcher\Validator\Formatter\ExceptionViolationFormatter;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Webmozart\Assert\Assert;

use function array_map;
use function iterator_to_array;

/** @api */
const included_violations = ViolationListExceptionFormatter::class;

/**
 * @api - use {@see included_violations} constant for a class reference instead
 *
 * @implements ExceptionViolationFormatter<ViolationListException>
 */
final class ViolationListExceptionFormatter implements ExceptionViolationFormatter
{
    /**
     * @param MatchedException<ViolationListException> $matchedException
     *
     * @return non-empty-list<ConstraintViolation>
     */
    public function format(MatchedException $matchedException): array
    {
        $exception = $matchedException->getException();
        Assert::isInstanceOf($exception, ViolationListException::class); // @phpstan-ignore staticMethod.alreadyNarrowedType

        $rule = $matchedException->getRule();
        $root = $rule->getRootObject();
        $propertyPath = $rule->getPropertyPath()
            ->join('.')
        ;
        /** @var list<ConstraintViolationInterface> $violationList */
        $violationList = iterator_to_array($exception->getViolationList());

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
