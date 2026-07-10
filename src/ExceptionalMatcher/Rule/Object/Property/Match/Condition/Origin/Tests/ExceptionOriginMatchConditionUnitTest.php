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

/**
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\ExceptionOriginMatchCondition
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Origin\ExceptionOriginMatchConditionFactory
 * @covers \PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\Composite\CompositeMatchConditionFactory
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
        $message = new OriginConditionMessage('non-email', 'uid');

        try {
            /** @psalm-suppress UnusedMethodCall */
            Email::fromString('non-email')->getEmail(); // @phpstan-ignore method.resultUnused

            self::fail('The exception must be thrown.');
        } catch (ValidationFailedException $originalException) {
        }

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('email', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testMatchExceptionByOriginClassMethod(): void
    {
        $message = new OriginConditionMessage('email', 'invalid-uuid');

        try {
            Uuid::fromString('invalid-uuid');

            self::fail('The exception must be thrown.');
        } catch (InvalidArgumentException $originalException) {
        }

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('uid', $matchedException->getRule()->getPropertyPath()->join('.'));
    }

    public function testMatchExceptionByOriginPropertyHook(): void
    {
        if (\PHP_VERSION_ID < 80400) {
            self::markTestSkipped('Property hooks require PHP 8.4.');
        }

        $message = new HookOriginConditionMessage('');
        $entity = new ProductHookedEntity();

        try {
            $entity->setTitle('');
            self::fail('The exception must be thrown.');
        } catch (ValidationFailedException $originalException) {
        }

        $matchedExceptionList = $this->matcher->match($originalException, $message);

        self::assertNotNull($matchedExceptionList);
        self::assertCount(1, $matchedExceptionList);

        [$matchedException] = $matchedExceptionList->toArray();

        self::assertSame('title', $matchedException->getRule()->getPropertyPath()->join('.'));
    }
}
