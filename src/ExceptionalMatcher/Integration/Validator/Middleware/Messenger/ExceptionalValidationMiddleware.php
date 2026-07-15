<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Middleware\Messenger;

use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Integration\Validator\Middleware\ExceptionalValidationFailedException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

/** @internal */
final class ExceptionalValidationMiddleware implements MiddlewareInterface
{
    /** @api */
    public function __construct(
        /** @var ExceptionMatcher<ConstraintViolationListInterface> */
        private readonly ExceptionMatcher $exceptionMatcher,
    ) {
    }

    /** @throws ExceptionalValidationFailedException|ExceptionInterface */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            return $stack->next()
                ->handle($envelope, $stack)
            ;
        } catch (Throwable $exception) {
            $message = $envelope->getMessage();

            $violationList = $this->exceptionMatcher->match($exception, $message);

            if (null === $violationList) {
                throw $exception;
            }

            throw new ExceptionalValidationFailedMessengerException($message, $violationList, $exception);
        }
    }
}
