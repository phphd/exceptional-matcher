<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\TraceOrigin\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionMapper\Bundle\DependencyInjection\PhdExceptionMapperExtension;
use PhPhD\ExceptionMapper\ExceptionMapper;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\TraceOrigin\Tests\Stub\Email;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\TraceOrigin\Tests\Stub\Hook\HookOriginConditionMessage;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\TraceOrigin\Tests\Stub\Hook\ProductHookedEntity;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\TraceOrigin\Tests\Stub\OriginConditionMessage;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionMapper\Tests\Trait\CatchException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\TraceOrigin\Tests\Stub\validate_email_string;

/**
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\TraceOrigin\ExceptionTraceOriginMatchCondition
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\TraceOrigin\ExceptionTraceOriginMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Blueprint\PreCompiledMatchConditionBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionBlueprint
 * @covers \PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Condition\Composite\MemoizingIteratorAggregate
 *
 * @internal
 */
final class ExceptionTraceOriginMatchConditionUnitTest extends TestCase
{
    use CatchException;

    /** @var ExceptionMapper<MatchedExceptionList> */
    private ExceptionMapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionMapperExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMapper<MatchedExceptionList> $mapper */
        $mapper = $container->get(ExceptionMapper::class.'<'.MatchedExceptionList::class.'>');
        $this->mapper = $mapper;
    }

    public function testMatchExceptionByOriginClass(): void
    {
        $message = new OriginConditionMessage(email: 'non-email');

        $emailValidationException = self::catchException(
            static fn(): string => Email::fromString('non-email')->getEmail(),
            ValidationFailedException::class,
        );

        $matchedExceptionList = $this->mapper->map($emailValidationException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('email', $matchedException->getCatchNode()->getPropertyPath()->join('.'));
    }

    public function testMatchExceptionByOriginClassMethod(): void
    {
        $message = new OriginConditionMessage(uid: 'invalid-uuid');

        $uidValidationException = self::catchException(
            static fn(): Uuid => Uuid::fromString('invalid-uuid'),
            InvalidArgumentException::class,
        );

        $matchedExceptionList = $this->mapper->map($uidValidationException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('uid', $matchedException->getCatchNode()->getPropertyPath()->join('.'));
    }

    public function testMatchExceptionByCallableStringOrigin(): void
    {
        $message = new OriginConditionMessage(anotherEmail: 'non-email');

        $originValidationException = self::catchException(
            static fn() => validate_email_string('non-email'),
            ValidationFailedException::class,
        );

        $matchedExceptionList = $this->mapper->map($originValidationException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('anotherEmail', $matchedException->getCatchNode()->getPropertyPath()->join('.'));
    }

    public function testMatchExceptionByOriginPropertyHook(): void
    {
        if (\PHP_VERSION_ID < 80400) {
            self::markTestSkipped('Property hooks require PHP 8.4.');
        }

        $entity = new ProductHookedEntity();
        $message = new HookOriginConditionMessage('');

        $titleValidationException = self::catchException(
            static fn() => $entity->setTitle(''),
            ValidationFailedException::class,
        );

        $matchedExceptionList = $this->mapper->map($titleValidationException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('title', $matchedException->getCatchNode()->getPropertyPath()->join('.'));
    }

    public function testDoesNotMatchExceptionThrownFromAnotherPropertyHook(): void
    {
        if (\PHP_VERSION_ID < 80400) {
            self::markTestSkipped('Property hooks require PHP 8.4.');
        }

        $entity = new ProductHookedEntity();
        $message = new HookOriginConditionMessage('invalid email');

        $entity->setTitle('invalid email');

        $emailValidationException = self::catchException(
            $entity->getEmailTitle(...),
            ValidationFailedException::class,
        );

        $matchedExceptionList = $this->mapper->map($emailValidationException, $message);

        self::assertNull($matchedExceptionList);
    }
}
