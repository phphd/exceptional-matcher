<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\Qualified\Tests\Stub;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatch;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\ExceptionMatchFormatter;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

/** @implements ExceptionMatchFormatter<Throwable,ConstraintViolationInterface> */
final class CustomExceptionViolationFormatter implements ExceptionMatchFormatter
{
    /** @api */
    public function __construct(
        /** @var ExceptionMatchFormatter<Throwable,ConstraintViolationInterface> */
        private readonly ExceptionMatchFormatter $formatter,
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
