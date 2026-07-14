<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\Tests;

use InvalidArgumentException;
use PhPhD\ExceptionalMatcher\Bundle\DependencyInjection\PhdExceptionalMatcherExtension;
use PhPhD\ExceptionalMatcher\Exception\MatchedExceptionList;
use PhPhD\ExceptionalMatcher\ExceptionMatcher;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\Tests\Stub\Email;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\Tests\Stub\Hook\HookOriginConditionMessage;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\Tests\Stub\Hook\ProductHookedEntity;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\Tests\Stub\OriginConditionMessage;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Exception\ValidationFailedException;

use function PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\Tests\Stub\validate_email_string;

/**
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Catch_
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\ExceptionOriginMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\ExceptionOriginMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\PreCompiledMatchConditionBlueprint
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionCompiler
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionBlueprint
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\ReusableIteratorAggregate
 *
 * @internal
 */
final class ExceptionOriginMatchConditionUnitTest extends TestCase
{
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

        try {
            /** @psalm-suppress UnusedMethodCall */
            Email::fromString('non-email')->getEmail(); // @phpstan-ignore method.resultUnused

            self::fail('The exception must be thrown.');
        } catch (ValidationFailedException $emailValidationException) {
        }

        $matchedExceptionList = $this->matcher->match($emailValidationException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('email', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testMatchExceptionByOriginClassMethod(): void
    {
        $message = new OriginConditionMessage(uid: 'invalid-uuid');

        try {
            Uuid::fromString('invalid-uuid');

            self::fail('The exception must be thrown.');
        } catch (InvalidArgumentException $uidValidationException) {
        }

        $matchedExceptionList = $this->matcher->match($uidValidationException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('uid', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testMatchExceptionByCallableStringOrigin(): void
    {
        $message = new OriginConditionMessage(anotherEmail: 'non-email');

        try {
            validate_email_string('non-email');

            self::fail('The exception must be thrown.');
        } catch (ValidationFailedException $originValidationException) {
        }

        $matchedExceptionList = $this->matcher->match($originValidationException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('anotherEmail', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testMatchExceptionByOriginPropertyHook(): void
    {
        if (\PHP_VERSION_ID < 80400) {
            self::markTestSkipped('Property hooks require PHP 8.4.');
        }

        $entity = new ProductHookedEntity();
        $message = new HookOriginConditionMessage('');

        try {
            $entity->setTitle('');
            self::fail('The exception must be thrown.');
        } catch (ValidationFailedException $titleValidationException) {
        }

        $matchedExceptionList = $this->matcher->match($titleValidationException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('title', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testDoesNotMatchExceptionThrownFromAnotherPropertyHook(): void
    {
        if (\PHP_VERSION_ID < 80400) {
            self::markTestSkipped('Property hooks require PHP 8.4.');
        }

        $entity = new ProductHookedEntity();
        $message = new HookOriginConditionMessage('invalid email');

        $entity->setTitle('invalid email');

        try {
            /** @noinspection PhpExpressionResultUnusedInspection */
            /** @psalm-suppress UnusedMethodCall */
            $entity->getEmailTitle();
            self::fail('The exception must be thrown.');
        } catch (ValidationFailedException $emailValidationException) {
        }

        $matchedExceptionList = $this->matcher->match($emailValidationException, $message);

        self::assertNull($matchedExceptionList);
    }
}
