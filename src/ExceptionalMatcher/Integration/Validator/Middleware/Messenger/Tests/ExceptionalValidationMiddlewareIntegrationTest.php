<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Validator\Middleware\Messenger\Tests;

use PhPhD\ExceptionalMatcher\Bundle\Tests\BundleTestCase;
use PhPhD\ExceptionalMatcher\Integration\Validator\Middleware\ExceptionalValidationFailedException;
use PhPhD\ExceptionalMatcher\Integration\Validator\Middleware\Messenger\ExceptionalValidationMiddleware;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\AnException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\Exception\StaticPropertyMatchedException;
use PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Throwable;

/**
 * @covers \PhPhD\ExceptionalMatcher\Integration\Validator\Middleware\Messenger\ExceptionalValidationMiddleware
 * @covers \PhPhD\ExceptionalMatcher\Integration\Validator\Middleware\Messenger\ExceptionalValidationFailedMessengerException
 *
 * @internal
 */
final class ExceptionalValidationMiddlewareIntegrationTest extends BundleTestCase
{
    private ExceptionalValidationMiddleware $middleware;

    private MockObject $nextMiddleware;

    private StackMiddleware $stack;

    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();

        /** @var ExceptionalValidationMiddleware $middleware */
        $middleware = $container->get('phd_exceptional_validation');

        $this->middleware = $middleware;

        $this->nextMiddleware = $this->createMock(MiddlewareInterface::class);
        $this->stack = new StackMiddleware([$this->middleware, $this->nextMiddleware]);
    }

    public function testReturnsResultEnvelopeWhenNoException(): void
    {
        $envelope = Envelope::wrap(HandleableMessageStub::create());
        $resultEnvelope = Envelope::wrap(new stdClass());

        $this->nextMiddleware
            ->method('handle')
            ->willReturnMap([[$envelope, $this->stack, $resultEnvelope]])
        ;

        $result = $this->middleware->handle($envelope, $this->stack);

        self::assertSame($resultEnvelope, $result);
    }

    public function testHandlesWrappedExceptionsOfHandlerFailedException(): void
    {
        $envelope = Envelope::wrap(HandleableMessageStub::create());

        $handlerException1 = new AnException();
        $handlerException2 = new StaticPropertyMatchedException();

        $messengerException = new HandlerFailedException(
            $envelope,
            [$handlerException1, new HandlerFailedException(
                $envelope,
                [$handlerException2],
            )],
        );

        $this->willThrow($messengerException);

        try {
            $this->middleware->handle($envelope, $this->stack);

            self::fail('The exception must be thrown.');
        } catch (ExceptionalValidationFailedException $e) {
        }

        self::assertSame(
            'Message of type "PhPhD\ExceptionalMatcher\Tests\Unit\Stub\HandleableMessageStub" has failed exceptional validation.',
            $e->getMessage(),
        );
        self::assertSame($messengerException, $e->getPrevious());
        self::assertSame($envelope->getMessage(), $e->getViolatingMessage());

        $violations = $e->getViolations();
        self::assertCount(2, $violations);

        self::assertSame('property', $violations->get(0)->getPropertyPath());
        self::assertSame('staticProperty', $violations->get(1)->getPropertyPath());
    }

    public function testHandlesNotWrappedException(): void
    {
        $envelope = Envelope::wrap(HandleableMessageStub::create());

        $handlerException = new AnException();

        $this->willThrow($handlerException);

        try {
            $this->middleware->handle($envelope, $this->stack);

            self::fail('The exception must be thrown.');
        } catch (ExceptionalValidationFailedException $e) {
        }

        self::assertSame($handlerException, $e->getPrevious());
    }

    public function testRethrowsUnhandledException(): void
    {
        $envelope = Envelope::wrap(HandleableMessageStub::create());

        $exception = new RuntimeException();

        $this->willThrow($exception);

        $this->expectExceptionObject($exception);

        $this->middleware->handle($envelope, $this->stack);
    }

    private function willThrow(Throwable $exception): void
    {
        $this->nextMiddleware
            ->method('handle')
            ->willThrowException($exception)
        ;
    }
}
