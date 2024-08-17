<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use LogicException;
use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;

use function array_map;
use function iterator_to_array;

final class ViolationListExceptionFormatter implements ExceptionViolationFormatter
{
    public function format(CapturedException $capturedException): array
    {
        $exception = $capturedException->getException();

        if (!$exception instanceof ViolationListException) {
            throw new LogicException('Violation list formatter could only be used for exception class that implement ViolationListException');
        }

        $rule = $capturedException->getMatchedRule();
        $root = $rule->getRoot();
        $propertyPath = $rule->getPropertyPath()->join('.');

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
