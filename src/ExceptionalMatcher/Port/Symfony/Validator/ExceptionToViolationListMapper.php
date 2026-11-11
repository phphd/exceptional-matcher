<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Port\Symfony\Validator;

use PhPhD\ExceptionalMatcher\ExceptionMapper;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMapper<ConstraintViolationListInterface>
 */
final class ExceptionToViolationListMapper implements ExceptionMapper
{
    /** @api */
    public function __construct(
        /** @var ExceptionMapper<MatchedExceptionList> */
        private readonly ExceptionMapper $mapper,
        /** @var MatchedExceptionFormatter<Throwable,ConstraintViolationInterface> */
        private readonly MatchedExceptionFormatter $formatter,
    ) {
    }

    public function map(Throwable $exception, object $object): ?ConstraintViolationListInterface
    {
        $matchedExceptionList = $this->mapper->map($exception, $object);

        if (null === $matchedExceptionList) {
            return null;
        }

        $violations = $matchedExceptionList->format($this->formatter);

        return new ConstraintViolationList($violations);
    }
}
