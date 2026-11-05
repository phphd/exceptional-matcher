<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification;

use ReflectionNamedType;

/** @internal */
final class StringType implements TypeSpecification
{
    private const TYPE_STRING = 'string';

    /** @phpstan-assert-if-true string $value */
    public function __invoke(mixed $value): bool
    {
        return is_string($value);
    }

    public function isSatisfiedBy(ReflectionNamedType $type): bool
    {
        return $type->isBuiltin() && self::TYPE_STRING === $type->getName();
    }
}
