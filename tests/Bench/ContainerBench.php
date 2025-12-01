<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalValidation\Tests\Bench;

use ArrayObject;
use LogicException;
use PhPhD\ExceptionalValidation\Bundle\DependencyInjection\PhdExceptionalValidationExtension;
use PhPhD\ExceptionalValidation\Mapper\ExceptionMapper;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\NestedItemCapturedException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\Exception\PropertyCapturableException;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\HandleableMessageStub;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NestedItem;
use PhPhD\ExceptionalValidation\Tests\Unit\Stub\NotHandleableMessageStub;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * @internal
 *
 * @api
 */
final readonly class ContainerBench
{
    public function __construct()
    {
        if (PhdExceptionalValidationExtension::nativeProxiesAreSupported()) {
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

        $message = new NotHandleableMessageStub(123);

        $violationList = $mapper->map($message, new PropertyCapturableException());

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

        $originalException = new NestedItemCapturedException(code: 2);

        /** @var ConstraintViolationListInterface $violationList */
        $violationList = $mapper->map($message, $originalException);

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
        $container = PhdExceptionalValidationExtension::getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ], $allowGeneratedProxies);

        $container->compile();

        return $container;
    }
}
