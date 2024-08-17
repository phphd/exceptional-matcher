<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter;

use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

use function array_merge;

/** @internal */
final class DefaultExceptionListViolationFormatter implements ExceptionListViolationFormatter
{
    public function __construct(
        private readonly ExceptionViolationFormatter $violationFormatter,
    ) {
    }

    /** @param non-empty-list<CapturedException> $capturedExceptionList */
    public function format(array $capturedExceptionList): ConstraintViolationListInterface
    {
        /** @var list<list<ConstraintViolationInterface>> $violations */
        $violations = [];

        foreach ($capturedExceptionList as $capturedException) {
            $violations[] = $this->violationFormatter->format($capturedException);
        }

        return new ConstraintViolationList(array_merge(...$violations));
    }
}
