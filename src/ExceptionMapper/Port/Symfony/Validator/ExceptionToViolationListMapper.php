<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Port\Symfony\Validator;

use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\ExceptionMatchList;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\Formatter\ExceptionMatchFormatter;
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
        /** @var ExceptionMapper<ExceptionMatchList> */
        private readonly ExceptionMapper $mapper,
        /** @var ExceptionMatchFormatter<Throwable,ConstraintViolationInterface> */
        private readonly ExceptionMatchFormatter $formatter,
    ) {
    }

    public function map(Throwable $exception, object $object): ?ConstraintViolationListInterface
    {
        $exceptionMatches = $this->mapper->map($exception, $object);

        if (null === $exceptionMatches) {
            return null;
        }

        $violations = $exceptionMatches->format($this->formatter);

        return new ConstraintViolationList($violations);
    }
}
