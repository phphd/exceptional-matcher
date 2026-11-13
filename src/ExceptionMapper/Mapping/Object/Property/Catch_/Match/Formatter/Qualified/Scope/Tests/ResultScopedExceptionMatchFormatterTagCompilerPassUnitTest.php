<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope\Tests;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope\ResultScopedExceptionMatchFormatterTagCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope\ResultScopedExceptionMatchFormatterTagCompilerPass
 *
 * @internal
 */
final class ResultScopedExceptionMatchFormatterTagCompilerPassUnitTest extends TestCase
{
    private const VIOLATION_FORMATTER_TAG = ExceptionMatchFormatter::class.'<?,'.ConstraintViolationInterface::class.'>';

    private const STRING_FORMATTER_TAG = ExceptionMatchFormatter::class.'<?,string>';

    private ContainerBuilder $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ContainerBuilder();
    }

    public function testScopesFormatterByItsOwnGenericId(): void
    {
        $formatterId = ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>';
        $this->registerTaggedFormatter($formatterId);

        $this->compile();

        self::assertSame([
            $formatterId => [[]],
        ], $this->container->findTaggedServiceIds(self::VIOLATION_FORMATTER_TAG));
    }

    public function testScopesFormatterByAliasChain(): void
    {
        $this->registerTaggedFormatter('custom_formatter');
        $this->container->setAlias('custom_formatter_alias', 'custom_formatter');
        $this->container->setAlias(ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>', 'custom_formatter_alias');

        $this->compile();

        self::assertSame([
            'custom_formatter' => [[]],
        ], $this->container->findTaggedServiceIds(self::VIOLATION_FORMATTER_TAG));
    }

    public function testScopesFormatterOncePerDistinctResultType(): void
    {
        $this->registerTaggedFormatter('custom_formatter');
        $this->container->setAlias(ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>', 'custom_formatter');
        $this->container->setAlias(ExceptionMatchFormatter::class.'<LogicException,'.ConstraintViolationInterface::class.'>', 'custom_formatter');
        $this->container->setAlias(ExceptionMatchFormatter::class.'<'.Throwable::class.',string>', 'custom_formatter');

        $this->compile();

        self::assertSame([
            'custom_formatter' => [[]],
        ], $this->container->findTaggedServiceIds(self::VIOLATION_FORMATTER_TAG));
        self::assertSame([
            'custom_formatter' => [[]],
        ], $this->container->findTaggedServiceIds(self::STRING_FORMATTER_TAG));
    }

    public function testLeavesFormatterOutOfAnotherResultTypeScope(): void
    {
        $this->registerTaggedFormatter('custom_formatter');
        $this->container->setAlias(ExceptionMatchFormatter::class.'<'.Throwable::class.',string>', 'custom_formatter');

        $this->compile();

        self::assertSame([], $this->container->findTaggedServiceIds(self::VIOLATION_FORMATTER_TAG));
    }

    public function testCopiesSourceTagAttributes(): void
    {
        $this->registerTaggedFormatter('custom_formatter', [
            'id' => 'custom',
        ]);
        $this->container->setAlias(ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>', 'custom_formatter');

        $this->compile();

        self::assertSame([
            'custom_formatter' => [[
                'id' => 'custom',
            ]],
        ], $this->container->findTaggedServiceIds(self::VIOLATION_FORMATTER_TAG));
    }

    /** @param array<string,string> $tagAttributes */
    private function registerTaggedFormatter(string $formatterId, array $tagAttributes = []): void
    {
        $this->container
            ->setDefinition($formatterId, new Definition(ExceptionMatchFormatter::class))
            ->addTag(ExceptionMatchFormatter::class, $tagAttributes)
        ;
    }

    private function compile(): void
    {
        (new ResultScopedExceptionMatchFormatterTagCompilerPass())->process($this->container);
    }
}
