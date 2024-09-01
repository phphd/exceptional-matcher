<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Middleware\Messenger;

use Exception;
use PhPhD\ExceptionalValidation\Handler\ExceptionHandler;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Throwable;

/** @internal */
final class ExceptionalValidationMiddleware implements MiddlewareInterface
{
    /** @api */
    public function __construct(
        private readonly ExceptionHandler $exceptionHandler,
    ) {
    }

    /** @throws Throwable */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            return $stack->next()->handle($envelope, $stack);
        } catch (Exception $exception) {
            $message = $envelope->getMessage();

            $this->exceptionHandler->capture($message, $exception);

            throw $exception;
        }
    }
}
