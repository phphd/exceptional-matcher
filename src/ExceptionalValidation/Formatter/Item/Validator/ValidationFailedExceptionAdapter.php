<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Formatter\Item\Validator;

use PhPhD\ExceptionalValidation\Formatter\Item\ViolationList\ViolationListException;
use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/** @internal */
final class ValidationFailedExceptionAdapter extends RuntimeException implements ViolationListException
{
    public function __construct(
        private readonly ValidationFailedException $validationFailedException,
    ) {
        parent::__construct(previous: $this->validationFailedException);
    }

    public function getViolationList(): ConstraintViolationListInterface
    {
        return $this->validationFailedException->getViolations();
    }
}
