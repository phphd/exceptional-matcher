<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Handler\Exception;

use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

use function sprintf;

final class ExceptionalValidationFailedException extends RuntimeException
{
    public function __construct(
        private readonly object $violatingMessage,
        private readonly ConstraintViolationListInterface $violations,
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

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
