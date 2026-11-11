<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Autoload;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\ConstantsAutoloadingClassDiscovery;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_keys;

/** @internal */
final class QualifiedExceptionMatchFormatterClassDiscovery implements ConstantsAutoloadingClassDiscovery
{
    /** @return array<class-string,true> */
    public function getClassNames(ContainerBuilder $container): array
    {
        $classNames = [];
        $taggedServiceIds = array_keys($container->findTaggedServiceIds(ExceptionMatchFormatter::class));

        foreach ($taggedServiceIds as $taggedServiceId) {
            $def = $container->getDefinition($taggedServiceId);

            /** @var class-string $className */
            $className = $def->getClass();

            $classNames[$className] = true;
        }

        return $classNames;
    }
}
