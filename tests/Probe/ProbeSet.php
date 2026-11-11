<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Tests\Probe;

use ReflectionClass;
use ReflectionProperty;
use UnitEnum;

final class ProbeSet
{
    public function __construct(
        /** @var list<class-string> */
        private readonly array $classNames,
    ) {
    }

    /** @return iterable<string,array{ReflectionProperty}> */
    public function of(UnitEnum $rule): iterable
    {
        foreach ($this->classNames as $className) {
            foreach ((new ReflectionClass($className))->getProperties() as $property) {
                $probe = self::probeOf($property);

                if (null === $probe || $probe->rule !== $rule) {
                    continue;
                }

                yield $rule->name.': '.($probe->label ?? self::referenceOf($property)) => [$property];
            }
        }
    }

    public static function referenceOf(ReflectionProperty $property): string
    {
        return $property->getDeclaringClass()->getName().'::$'.$property->getName();
    }

    private static function probeOf(ReflectionProperty $property): ?ProbeOf
    {
        /** @var ?ProbeOf */
        return ($property->getAttributes(ProbeOf::class)[0] ?? null)?->newInstance();
    }
}
