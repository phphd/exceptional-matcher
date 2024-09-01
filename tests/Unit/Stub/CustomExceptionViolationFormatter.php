<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Unit\Stub;

use PhPhD\ExceptionalValidation\Formatter\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Model\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;

use function ucfirst;

final class CustomExceptionViolationFormatter implements ExceptionViolationFormatter
{
    public function __construct(
        private readonly ExceptionViolationFormatter $formatter,
    ) {
    }

    /** @return array{ConstraintViolation} */
    public function format(CapturedException $capturedException): array
    {
        [$violation] = $this->formatter->format($capturedException);

        return [
            new ConstraintViolation(
                'custom - '.$violation->getMessage(),
                'custom.'.$violation->getMessageTemplate(),
                [
                    'custom' => 'param',
                ],
                $violation->getRoot(),
                'custom'.ucfirst($violation->getPropertyPath()),
                $violation->getInvalidValue(),
            ),
        ];
    }
}
