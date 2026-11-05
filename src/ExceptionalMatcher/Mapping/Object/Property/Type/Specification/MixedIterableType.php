<?php /** @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection */

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Type\Specification;

use ReflectionNamedType;
use Traversable;

/** @internal */
final class MixedIterableType implements TypeSpecification
{
    /** @phpstan-assert-if-true iterable<mixed> $value */
    public function __invoke(mixed $value): bool
    {
        return is_iterable($value);
    }

    public function isSatisfiedBy(ReflectionNamedType $type): bool
    {
        $name = $type->getName();

        if ($type->isBuiltin()) {
            return in_array($name, ['array', 'iterable'], strict: true);
        }

        return is_a($name, Traversable::class, allow_string: true);
    }
}
