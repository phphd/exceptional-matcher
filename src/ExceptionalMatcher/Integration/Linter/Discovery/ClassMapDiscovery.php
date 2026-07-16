<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Integration\Linter\Discovery;

use Composer\ClassMapGenerator\ClassMapGenerator;
use Generator;
use RuntimeException;
use Throwable;

use function array_keys;
use function class_exists;
use function enum_exists;

/**
 * Discovers the classes to lint within the given files or directories.
 *
 * @api
 */
final class ClassMapDiscovery
{
    /**
     * @param list<string> $paths
     *
     * @return Generator<int,class-string>
     */
    public function discover(array $paths): Generator
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

        $classMap = $classMapGenerator->getClassMap();

        foreach (array_keys($classMap->getMap()) as $className) {
            if (!$this->isLintableClass($className)) {
                continue;
            }

            yield $className;
        }
    }

    /** Interfaces, traits, enums, and files that fail to load have no `#[Catch_]` properties to lint. */
    private function isLintableClass(string $className): bool
    {
        try {
            return class_exists($className) && !enum_exists($className);
        } catch (Throwable) {
            return false;
        }
    }
}
