<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Handler\Exception;

use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

use function sprintf;

/** @api */
final class ExceptionalValidationFailedException extends RuntimeException
{
    public function __construct(
        private readonly object $violatingMessage,
        private readonly ConstraintViolationListInterface $violationList,
        Throwable $previous,
    ) {
        parent::__construct(
            sprintf('Message of type "%s" has failed exceptional validation.', $this->violatingMessage::class),
            previous: $previous,
        );
    }

    public function getViolatingMessage(): object
    {
        return $this->violatingMessage;
    }

    public function getViolationList(): ConstraintViolationListInterface
    {
        return $this->violationList;
    }
}
