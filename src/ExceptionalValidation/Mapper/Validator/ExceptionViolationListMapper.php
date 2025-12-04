<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Mapper\Validator;

use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Mapper\Validator\Formatter\List\PropriatedExceptionListFormatter;
use PhPhD\ExceptionalValidation\Rule\Exception\PropriatedException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/**
 * @internal
 *
 * @implements ExceptionMapper<ConstraintViolationListInterface>
 */
final readonly class ExceptionViolationListMapper implements ExceptionMapper
{
    /** @api */
    public function __construct(
        /** @var ExceptionMapper<non-empty-list<PropriatedException<Throwable>>> */
        private ExceptionMapper $mapper,
        private PropriatedExceptionListFormatter $exceptionListFormatter,
    ) {
    }

    public function map(object $message, Throwable $exception): ?ConstraintViolationListInterface
    {
        $propriatedExceptionList = $this->mapper->map($message, $exception);

        if (null === $propriatedExceptionList) {
            return null;
        }

        return $this->exceptionListFormatter->format($propriatedExceptionList);
    }
}
