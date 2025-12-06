<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator;

use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Rule\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\MatchedExceptionList;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMapper<ConstraintViolationListInterface>
 */
final readonly class ExceptionToViolationListMapper implements ExceptionMapper
{
    /** @api */
    public function __construct(
        /** @var ExceptionMapper<MatchedExceptionList> */
        private ExceptionMapper $mapper,
        /** @var MatchedExceptionFormatter<Throwable,ConstraintViolationInterface> */
        private MatchedExceptionFormatter $formatter,
    ) {
    }

    public function map(object $message, Throwable $exception): ?ConstraintViolationListInterface
    {
        $matchedExceptionList = $this->mapper->map($message, $exception);

        if (null === $matchedExceptionList) {
            return null;
        }

        $violations = $matchedExceptionList->format($this->formatter);

        return new ConstraintViolationList($violations);
    }
}
