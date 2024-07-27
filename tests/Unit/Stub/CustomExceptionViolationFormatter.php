<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Stub;

use PhPhD\ExceptionalValidation\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;

use function ucfirst;

final class CustomExceptionViolationFormatter implements ExceptionViolationFormatter
{
    public function __construct(
        private readonly ExceptionViolationFormatter $formatter,
    ) {
    }

    public function formatViolation(CapturedException $capturedException): ConstraintViolationInterface
    {
        $violation = $this->formatter->formatViolation($capturedException);

        return new ConstraintViolation(
            'custom - '.$violation->getMessage(),
            'custom.'.$violation->getMessageTemplate(),
            [
                'custom' => 'param',
            ],
            $violation->getRoot(),
            'custom'.ucfirst($violation->getPropertyPath()),
            $violation->getInvalidValue(),
        );
    }
}
