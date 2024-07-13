<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Handler\Exception;

use PhPhD\ExceptionalValidation\Model\Exception\Adapter\ThrownException;
use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

use function sprintf;

/** @api */
final class ExceptionalValidationFailedException extends RuntimeException
{
    public function __construct(
        private readonly object $violatingMessage,
        private readonly ConstraintViolationListInterface $violations,
        ThrownException $thrownException,
    ) {
        parent::__construct(
            sprintf('Message of type "%s" has failed exceptional validation.', $this->violatingMessage::class),
            previous: $thrownException->getOriginalException(),
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
