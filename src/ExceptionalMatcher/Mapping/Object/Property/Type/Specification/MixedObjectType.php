<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification;

use ReflectionNamedType;

use function is_object;

/** @internal */
final class MixedObjectType implements TypeSpecification
{
    private const TYPE_OBJECT = 'object';

    /** @phpstan-assert-if-true object $value */
    public function __invoke(mixed $value): bool
    {
        return is_object($value);
    }

    public function isSatisfiedBy(ReflectionNamedType $type): bool
    {
        return $type->isBuiltin() && self::TYPE_OBJECT === $type->getName();
    }
}
