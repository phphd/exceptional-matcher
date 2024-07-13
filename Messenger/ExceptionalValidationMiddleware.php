<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidationBundle\Messenger;

use Exception;
use PhPhD\ExceptionalValidation\Handler\ExceptionHandler;
use PhPhD\ExceptionalValidation\Model\Exception\Adapter\SingleThrownException;
use PhPhD\ExceptionalValidationBundle\Messenger\Adapter\MessengerThrownException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\WrappedExceptionsInterface;
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
        $message = $envelope->getMessage();

        try {
            return $stack->next()->handle($envelope, $stack);
        } catch (WrappedExceptionsInterface $exception) {
            /** @var WrappedExceptionsInterface&Throwable $exception */

            $thrownException = new MessengerThrownException($exception);

            $this->exceptionHandler->capture($message, $thrownException);
        } catch (Exception $exception) {
            $thrownException = new SingleThrownException($exception);

            $this->exceptionHandler->capture($message, $thrownException);
        }

        throw $exception;
    }
}
