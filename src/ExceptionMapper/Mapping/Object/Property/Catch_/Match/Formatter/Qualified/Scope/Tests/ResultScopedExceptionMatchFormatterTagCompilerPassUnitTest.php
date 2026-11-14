<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope\Tests;

use LogicException;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope\ExceptionMatchFormatterClassTagCompilerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Throwable;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope\ExceptionMatchFormatterClassTagCompilerPass
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope\ExceptionMatchFormatterDefinition
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope\ExceptionMatchFormatterClassName
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

    public function testRejectsBareOwnId(): void
    {
        $this->registerTaggedFormatter(ExceptionMatchFormatter::class);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(ExceptionMatchFormatter::class.'<TException,TResult>');

        $this->compile();
    }

    public function testRejectsBareAliasId(): void
    {
        $this->registerTaggedFormatter('custom_formatter');
        $this->container->setAlias(ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>', 'custom_formatter');
        $this->container->setAlias(ExceptionMatchFormatter::class, 'custom_formatter');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage(ExceptionMatchFormatter::class.'<TException,TResult>');

        $this->compile();
    }

    /** @return iterable<string,array{string}> */
    public static function malformedGenericIds(): iterable
    {
        yield 'one argument' => [ExceptionMatchFormatter::class.'<'.ConstraintViolationInterface::class.'>'];

        yield 'three arguments' => [ExceptionMatchFormatter::class.'<'.Throwable::class.',string,string>'];

        yield 'empty argument' => [ExceptionMatchFormatter::class.'<'.Throwable::class.',>'];

        yield 'nested argument' => [ExceptionMatchFormatter::class.'<'.Throwable::class.',list<string>>'];

        yield 'whitespace' => [ExceptionMatchFormatter::class.'<'.Throwable::class.', string>'];

        yield 'unclosed' => [ExceptionMatchFormatter::class.'<'.Throwable::class.',string'];
    }

    /** @dataProvider malformedGenericIds */
    public function testRejectsMalformedGenericId(string $malformedGenericId): void
    {
        $this->registerTaggedFormatter('custom_formatter');
        $this->container->setAlias(ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>', 'custom_formatter');
        $this->container->setAlias($malformedGenericId, 'custom_formatter');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage($malformedGenericId);
        $this->expectExceptionMessage(ExceptionMatchFormatter::class.'<TException,TResult>');

        $this->compile();
    }

    public function testRejectsUntypedFormatter(): void
    {
        $this->registerTaggedFormatter('custom_formatter');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("#[AsAlias(ExceptionMatchFormatter::class.'<TException,TResult>')]");

        $this->compile();
    }

    public function testIgnoresIdSharingOnlyTheClassPrefix(): void
    {
        $this->registerTaggedFormatter('custom_formatter');
        $this->container->setAlias(ExceptionMatchFormatter::class.'<'.Throwable::class.','.ConstraintViolationInterface::class.'>', 'custom_formatter');
        $this->container->setAlias(ExceptionMatchFormatter::class.'Factory', 'custom_formatter');

        $this->compile();

        self::assertSame([
            'custom_formatter' => [[]],
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
        (new ExceptionMatchFormatterClassTagCompilerPass())->process($this->container);
    }
}
