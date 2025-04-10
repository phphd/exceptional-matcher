<?php

/** @noinspection SenselessProxyMethodInspection */
/** @noinspection PhpRedundantMethodOverrideInspection */

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Middleware\Messenger;

use Exception;
use PhPhD\ExceptionalValidation\Middleware\ExceptionalValidationFailedException;
use Symfony\Component\Messenger\Exception\ValidationFailedException as ValidationFailedMessengerException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

use function sprintf;

/** @internal */
final class ExceptionalValidationFailedMessengerException extends ValidationFailedMessengerException implements ExceptionalValidationFailedException
{
    public function __construct(
        object $violatingMessage,
        private readonly ConstraintViolationListInterface $violationList,
        Throwable $previous,
    ) {
        parent::__construct($violatingMessage, $violationList);

        Exception::__construct(sprintf('Message of type "%s" has failed exceptional validation.', $violatingMessage::class), previous: $previous);
    }

    /** Declaring the return type to be compatible with the interface */
    public function getViolatingMessage(): object
    {
        return parent::getViolatingMessage();
    }

    public function getViolationList(): ConstraintViolationListInterface
    {
        return $this->violationList;
    }
}
