<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\Delegating\Tests\Stub;

use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\Item\ExceptionViolationFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\CapturedException;
use Symfony\Component\Validator\ConstraintViolation;
use Throwable;

/** @implements ExceptionViolationFormatter<Throwable> */
final readonly class CustomExceptionViolationFormatter implements ExceptionViolationFormatter
{
    /** @api */
    public function __construct(
        /** @var ExceptionViolationFormatter<Throwable> */
        private ExceptionViolationFormatter $formatter,
    ) {
    }

    /** @return array{ConstraintViolation} */
    public function format(CapturedException $capturedException): array
    {
        [$violation] = $this->formatter->format($capturedException);

        /** @psalm-suppress ImplicitToStringCast */
        return [
            new ConstraintViolation(
                'custom - '.$violation->getMessage(),
                'custom.'.$violation->getMessageTemplate(),
                [
                    'custom' => 'param',
                ],
                $violation->getRoot(),
                'custom.'.$violation->getPropertyPath(),
                $violation->getInvalidValue(),
            ),
        ];
    }
}
