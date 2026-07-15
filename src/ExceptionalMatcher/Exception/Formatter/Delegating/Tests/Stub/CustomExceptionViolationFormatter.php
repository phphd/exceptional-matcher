<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Exception\Formatter\Delegating\Tests\Stub;

use PhPhD\ExceptionalMatcher\Exception\MatchedException;
use PhPhD\ExceptionalMatcher\Integration\Validator\Formatter\ExceptionViolationFormatter;
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
    public function format(MatchedException $matchedException): array
    {
        [$violation] = $this->formatter->format($matchedException);

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
