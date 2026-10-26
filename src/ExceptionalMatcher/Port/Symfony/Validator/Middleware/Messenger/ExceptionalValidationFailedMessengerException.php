<?php

/** @noinspection SenselessProxyMethodInspection */
/** @noinspection PhpRedundantMethodOverrideInspection */

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Port\Symfony\Validator\Middleware\Messenger;

use Exception;
use PhPhD\ExceptionalMatcher\Port\Symfony\Validator\Middleware\ExceptionalValidationFailedException;
use Symfony\Component\Messenger\Exception\ValidationFailedException as ValidationFailedMessengerException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

use function sprintf;

/** @internal */
final class ExceptionalValidationFailedMessengerException extends ValidationFailedMessengerException implements ExceptionalValidationFailedException
{
    public function __construct(
        object $violatingMessage,
        ConstraintViolationListInterface $violations,
        Throwable $previous,
    ) {
        parent::__construct($violatingMessage, $violations);

        Exception::__construct(sprintf('Message of type "%s" has failed exceptional validation.', $violatingMessage::class), previous: $previous);
    }

    /** Overriding to declare a return type compatible with the interface */
    public function getViolatingMessage(): object
    {
        return parent::getViolatingMessage();
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return parent::getViolations();
    }
}
