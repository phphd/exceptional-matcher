<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating\Tests\Stub;

use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

/** @implements MatchedExceptionFormatter<Throwable, ConstraintViolationInterface> */
final readonly class CustomExceptionFormatter implements MatchedExceptionFormatter
{
    /** @api */
    public function __construct(
        /** @var MatchedExceptionFormatter<Throwable,ConstraintViolationInterface> */
        private MatchedExceptionFormatter $formatter,
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
