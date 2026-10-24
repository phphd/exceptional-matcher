<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Origin\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Origin\Tests\Stub\Email;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Origin\Tests\Stub\Hook\HookOriginConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Origin\Tests\Stub\Hook\ProductHookedEntity;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Origin\Tests\Stub\OriginConditionMessage;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\Tests\Trait\CatchesExceptions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Origin\Tests\Stub\validate_email_string;

/**
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Origin\ExceptionOriginMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Origin\ExceptionOriginMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\PreCompiledMatchConditionPlan
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\CompositeMatchConditionPlan
 * @covers \PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Composite\ReusableIteratorAggregate
 *
 * @internal
 */
final class ExceptionOriginMatchConditionUnitTest extends TestCase
{
    use CatchesExceptions;

    /** @var ExceptionMatcher<MatchedExceptionList> */
    private ExceptionMatcher $matcher;

    protected function setUp(): void
    {
        parent::setUp();

        $container = (new PhdExceptionalMatcherExtension())->getContainer([
            'kernel.environment' => 'test',
            'kernel.build_dir' => __DIR__.'/var',
        ]);

        $container->compile();

        /** @var ExceptionMatcher<MatchedExceptionList> $matcher */
        $matcher = $container->get(ExceptionMatcher::class.'<'.MatchedExceptionList::class.'>');
        $this->matcher = $matcher;
    }

    public function testMatchExceptionByOriginClass(): void
    {
        $message = new OriginConditionMessage(email: 'non-email');

        $emailValidationException = self::catchException(
            static fn (): string => Email::fromString('non-email')->getEmail(),
            ValidationFailedException::class,
        );

        $matchedExceptionList = $this->matcher->match($emailValidationException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('email', $matchedException->getCatchNode()->getPropertyPath()->join('.'));
    }

    public function testMatchExceptionByOriginClassMethod(): void
    {
        $message = new OriginConditionMessage(uid: 'invalid-uuid');

        $uidValidationException = self::catchException(
            static fn (): Uuid => Uuid::fromString('invalid-uuid'),
            InvalidArgumentException::class,
        );

        $matchedExceptionList = $this->matcher->match($uidValidationException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('uid', $matchedException->getCatchNode()->getPropertyPath()->join('.'));
    }

    public function testMatchExceptionByCallableStringOrigin(): void
    {
        $message = new OriginConditionMessage(anotherEmail: 'non-email');

        $originValidationException = self::catchException(
            static fn () => validate_email_string('non-email'),
            ValidationFailedException::class,
        );

        $matchedExceptionList = $this->matcher->match($originValidationException, $message);

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
            static fn () => $entity->setTitle(''),
            ValidationFailedException::class,
        );

        $matchedExceptionList = $this->matcher->match($titleValidationException, $message);

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

        $matchedExceptionList = $this->matcher->match($emailValidationException, $message);

        self::assertNull($matchedExceptionList);
    }
}
