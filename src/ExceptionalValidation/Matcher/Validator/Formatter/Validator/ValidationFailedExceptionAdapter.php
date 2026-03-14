<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Matcher\Validator\Formatter\Validator;

use Exception;
use PhPhD\ExceptionalValidation\Matcher\Validator\Formatter\ViolationList\ViolationListException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @internal
 *
 * @warning it's neither possible to set $this->trace nor to override getTrace().
 */
final class ValidationFailedExceptionAdapter extends Exception implements ViolationListException
{
    public function __construct(
        private readonly ValidationFailedException $validationFailedException,
    ) {
        parent::__construct(
            $this->validationFailedException->getMessage(),
            $this->validationFailedException->getCode(),
            $this->validationFailedException->getPrevious(),
        );
        $this->file = $this->validationFailedException->getFile();
        $this->line = $this->validationFailedException->getLine();
    }

    public function __toString(): string
    {
        return $this->validationFailedException->__toString();
    }

    public function getViolationList(): ConstraintViolationListInterface
    {
        return $this->validationFailedException->getViolations();
    }
}
