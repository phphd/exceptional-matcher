<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatch;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Symfony\Validator\ExceptionViolationFormatter;
use Symfony\Component\Validator\ConstraintViolation;
use Throwable;

/** @implements ExceptionViolationFormatter<Throwable> */
final class CustomExceptionViolationFormatter implements ExceptionViolationFormatter
{
    /** @api */
    public function __construct(
        /** @var ExceptionViolationFormatter<Throwable> */
        private readonly ExceptionViolationFormatter $formatter,
    ) {
    }

    /** @return array{ConstraintViolation} */
    public function format(ExceptionMatch $exceptionMatch): array
    {
        [$violation] = $this->formatter->format($exceptionMatch);

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
