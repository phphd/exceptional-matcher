<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Linter\Class\Port\Path\Discovery;

use Composer\ClassMapGenerator\ClassMapGenerator;
use RuntimeException;

use function array_keys;
use function class_exists;

/** @internal */
final class ClassNameDiscovery
{
    /**
     * @param iterable<string> $paths
     *
     * @return array<int,class-string>
     */
    public function discover(iterable $paths): array
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

        return array_keys($classMapGenerator->getClassMap()->getMap());
    }
}
