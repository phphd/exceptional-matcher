<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Middleware\Messenger;

use Exception;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Middleware\ExceptionalValidationFailedException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/** @internal */
final class ExceptionalValidationMiddleware implements MiddlewareInterface
{
    /** @api */
    public function __construct(
        /** @var ExceptionMapper<ConstraintViolationListInterface> */
        private readonly ExceptionMapper $exceptionMapper,
    ) {
    }

    /** @throws ExceptionalValidationFailedException|ExceptionInterface */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            return $stack->next()->handle($envelope, $stack);
        } catch (Exception $exception) {
            $message = $envelope->getMessage();

            $violationList = $this->exceptionMapper->map($message, $exception);

            if (null === $violationList) {
                throw $exception;
            }

            throw new ExceptionalValidationFailedMessengerException($message, $violationList, $exception);
        }
    }
}
