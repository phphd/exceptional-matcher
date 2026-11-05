<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification;

use ReflectionNamedType;

/** @internal */
final class IntType implements TypeSpecification
{
    private const TYPE_INT = 'int';

    /** @phpstan-assert-if-true int $value */
    public function __invoke(mixed $value): bool
    {
        return is_int($value);
    }

    public function isSatisfiedBy(ReflectionNamedType $type): bool
    {
        return $type->isBuiltin() && self::TYPE_INT === $type->getName();
    }
}
