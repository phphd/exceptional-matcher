<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Tests\Bench;

use ArrayObject;
use LogicException;
use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\AnException;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\Exception\NestedItemMatchedException;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\MessageWithNoTryAttribute;
use PhPhD\ExceptionMapper\Tests\Unit\Stub\NestedItem;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @internal
 *
 * @api
 */
final class ContainerBench
{
    public function __construct()
    {
        if (PhdExceptionMapperExtension::nativeProxiesAreSupported()) {
            throw new LogicException('This bench is only useful for PHP <= 8.4 (when there were no native proxies)');
        }
    }

    /**
     * @revs(500)
     *
     * @iterations(3)
     *
     * @RetryThreshold(3.0)
     *
     * @ParamProviders("provideProxy")
     *
     * @param array{bool} $params
     */
    public function benchNotCatchException(array $params): void
    {
        [$isProxyAllowed] = $params;

        $mapper = $this->createMapper($isProxyAllowed);

        $message = new MessageWithNoTryAttribute(123);
        $exception = new AnException();

        $violationList = $mapper->map($exception, $message);

        if (null !== $violationList) {
            throw new RuntimeException('Expected to have no violations');
        }
    }

    /**
     * @revs(500)
     *
     * @iterations(3)
     *
     * @RetryThreshold(3.0)
     *
     * @ParamProviders("provideProxy")
     *
     * @param array{bool} $params
     */
    public function benchCatchException(array $params): void
    {
        [$isProxyAllowed] = $params;

        $mapper = $this->createMapper($isProxyAllowed);

        $message = HandleableMessageStub::create()->withNestedIterableItems(new ArrayObject([
            'first' => new NestedItem(1),
            'second' => new NestedItem(2),
            'third' => new NestedItem(3),
            4 => new NestedItem(2),
        ]));
        $originalException = new NestedItemMatchedException(code: 2);

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $mapper->map($originalException, $message);

        if (1 !== $violationList->count()) {
            throw new RuntimeException('Expected to have 1 violation');
        }
    }

    /** @return array<string, array{bool}> */
    public function provideProxy(): array
    {
        return [
            'gen proxy' => [true],
            'no proxy' => [false],
        ];
    }

    /** @return ExceptionMapper<ConstraintViolationListInterface> */
    private function createMapper(bool $allowGeneratedProxies): ExceptionMapper
    {
        $container = $this->createContainer($allowGeneratedProxies);

        /** @var ExceptionMapper<ConstraintViolationListInterface> */
        return $container->get(ExceptionMapper::class.'<'.ConstraintViolationListInterface::class.'>');
    }

    private function createContainer(bool $allowGeneratedProxies): ContainerBuilder
    {
        $container = (new PhdExceptionMapperExtension($allowGeneratedProxies))->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        return $container;
    }
}
