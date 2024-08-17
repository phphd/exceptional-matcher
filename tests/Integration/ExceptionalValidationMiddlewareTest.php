<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidationBundle\Tests;

use PhPhD\ExceptionalValidation\Handler\Exception\ExceptionalValidationFailedException;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\PropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Stub\Exception\StaticPropertyCapturedException;
use PhPhD\ExceptionalValidation\Tests\Stub\HandleableMessageStub;
use PhPhD\ExceptionalValidationBundle\Messenger\ExceptionalValidationMiddleware;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Throwable;

/**
 * @covers \PhPhD\ExceptionalValidationBundle\Messenger\ExceptionalValidationMiddleware
 * @covers \PhPhD\ExceptionalValidationBundle\Messenger\Adapter\MessengerThrownException
 * @covers \PhPhD\ExceptionalValidation\Model\Exception\Adapter\SingleThrownException
 *
 * @internal
 */
final class ExceptionalValidationMiddlewareTest extends TestCase
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

        $handlerException1 = new PropertyCapturableException();
        $handlerException2 = new StaticPropertyCapturedException();

        $messengerException = new HandlerFailedException($envelope, [$handlerException1, $handlerException2]);

        $this->willThrow($messengerException);

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->middleware->handle($envelope, $this->stack);
        } catch (ExceptionalValidationFailedException $e) {
            self::assertSame($messengerException, $e->getPrevious());

            $violations = $e->getViolationList();
            self::assertCount(2, $violations);

            self::assertSame('property', $violations->get(0)->getPropertyPath());
            self::assertSame('staticProperty', $violations->get(1)->getPropertyPath());

            throw $e;
        }
    }

    public function testHandlesNotWrappedException(): void
    {
        $envelope = Envelope::wrap(HandleableMessageStub::create());

        $handlerException = new PropertyCapturableException();

        $this->willThrow($handlerException);

        $this->expectException(ExceptionalValidationFailedException::class);

        try {
            $this->middleware->handle($envelope, $this->stack);
        } catch (ExceptionalValidationFailedException $e) {
            self::assertSame($handlerException, $e->getPrevious());

            throw $e;
        }
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
