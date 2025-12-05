<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Rule\Exception\Formatter\Delegating\Tests\Stub;

use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\PropriatedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

/** @implements PropriatedExceptionFormatter<Throwable, ConstraintViolationInterface> */
final readonly class CustomExceptionFormatter implements PropriatedExceptionFormatter
{
    /** @api */
    public function __construct(
        /** @var PropriatedExceptionFormatter<Throwable,ConstraintViolationInterface> */
        private PropriatedExceptionFormatter $formatter,
    ) {
    }

    /** @return array{ConstraintViolation} */
    public function format(PropriatedException $propriatedException): array
    {
        [$violation] = $this->formatter->format($propriatedException);

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
