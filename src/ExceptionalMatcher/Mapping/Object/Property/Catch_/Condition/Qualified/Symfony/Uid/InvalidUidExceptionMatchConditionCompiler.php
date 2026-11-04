<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Qualified\Symfony\Uid;

use LogicException;
use PhPhD\ExceptionalMatcher\Mapping\ExceptionMappingNode;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionBlueprint;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Blueprint\MatchConditionCompiler;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Bool\FalseCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\MatchCondition;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\PropertyTypeAnalyser;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\AnyType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\MixedType;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification\StringType;
use ReflectionProperty;
use ReflectionType;
use Symfony\Component\Uid\Exception\InvalidArgumentException as InvalidUidException;
use Webmozart\Assert\Assert;

use function is_string;
use function sprintf;

/** @api */
const uid_value = InvalidUidExceptionMatchCondition::class;

/**
 * @internal
 *
 * @implements MatchConditionCompiler<InvalidUidException>
 * @implements MatchConditionBlueprint<InvalidUidException>
 */
final class InvalidUidExceptionMatchConditionCompiler implements MatchConditionCompiler, MatchConditionBlueprint
{
    public const TYPE_GUARANTEE_MESSAGE = 'InvalidUidExceptionMatchCondition can only be used for '.InvalidUidException::class;

    /** @return MatchConditionBlueprint<InvalidUidException> */
    public function compile(ReflectionProperty $property, Catch_ $catch): MatchConditionBlueprint
    {
        /** @phpstan-ignore staticMethod.alreadyNarrowedType */
        Assert::isAOf($catch->getExceptionClass(), InvalidUidException::class, self::TYPE_GUARANTEE_MESSAGE);

        self::assertTypeCoversString($property->getType());

        return $this;
    }

    /** @return MatchCondition<InvalidUidException> */
    public function bind(ExceptionMappingNode $node): MatchCondition
    {
        $value = $node->getValue();

        if (!is_string($value)) {
            /** @psalm-var FalseCondition<InvalidUidException> */
            return new FalseCondition();
        }

        return new InvalidUidExceptionMatchCondition($value);
    }

    private static function assertTypeCoversString(?ReflectionType $type): void
    {
        $stringOrMixed = new AnyType([new StringType(), new MixedType()]);

        if (!(new PropertyTypeAnalyser($type))->covers($stringOrMixed)) {
            throw new LogicException(sprintf('InvalidUidExceptionMatchCondition requires a string property, got: %s', (string)$type));
        }
    }
}
