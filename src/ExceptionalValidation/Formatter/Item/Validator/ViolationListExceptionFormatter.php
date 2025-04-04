<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter\Item\Validator;

use LogicException;
use PhPhD\ExceptionalValidation\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Throwable;

use function array_map;
use function iterator_to_array;

/** @api */
final class ViolationListExceptionFormatter implements ExceptionViolationFormatter
{
    public function format(CapturedException $capturedException): array
    {
        $exception = $capturedException->getException();

        $rule = $capturedException->getMatchedRule();
        $root = $rule->getRoot();
        $propertyPath = $rule->getPropertyPath()->join('.');

        /** @var list<ConstraintViolationInterface> $violationList */
        $violationList = iterator_to_array($this->getViolationList($exception));

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

    private function getViolationList(Throwable $exception): ConstraintViolationListInterface
    {
        return match (true) {
            $exception instanceof ViolationListException => $exception->getViolationList(),
            $exception instanceof ValidationFailedException => $exception->getViolations(),
            default => throw new LogicException('Violation list formatter could only be used for exceptions that implement ViolationListException or those with built-in support'),
        };
    }
}
