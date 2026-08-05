<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Discovery;

use Composer\ClassMapGenerator\ClassMapGenerator;
use RuntimeException;
use Throwable;

use UnitEnum;

use function array_filter;
use function array_keys;
use function class_exists;
use function is_subclass_of;

/** @internal */
final class ClassNameDiscovery
{
    /**
     * @param list<string> $paths
     *
     * @return array<int,class-string>
     */
    public function discover(array $paths): array
    {
        if (!class_exists(ClassMapGenerator::class)) {
            throw new RuntimeException(
                'Class discovery requires the "composer/class-map-generator" package.'
                .' Try running "composer require --dev composer/class-map-generator".',
            );
        }

        $classMapGenerator = new ClassMapGenerator();

        foreach ($paths as $path) {
            $classMapGenerator->scanPaths($path);
        }

        $classNames = array_keys($classMapGenerator->getClassMap()->getMap());

        return array_filter($classNames, $this->isLintableClass(...));
    }

    /** Interfaces, traits, enums, and files that fail to load have no `#[Catch_]` properties to lint. */
    private function isLintableClass(string $className): bool
    {
        try {
            return class_exists($className)
                && !is_subclass_of($className, UnitEnum::class);
        } catch (Throwable) {
            return false;
        }
    }
}
