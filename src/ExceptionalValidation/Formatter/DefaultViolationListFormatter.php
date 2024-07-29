<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @internal */
final class DefaultViolationListFormatter implements ExceptionViolationListFormatter
{
    public function __construct(
        private readonly ExceptionViolationFormatter $violationFormatter,
    ) {
    }

    /** @param non-empty-list<CapturedException> $capturedExceptions */
    public function formatViolations(array $capturedExceptions): ConstraintViolationListInterface
    {
        $violations = new ConstraintViolationList();

        foreach ($capturedExceptions as $capturedException) {
            $violation = $this->violationFormatter->formatViolation($capturedException);

            $violations->add($violation);
        }

        return $violations;
    }
}
