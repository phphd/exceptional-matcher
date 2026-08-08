<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload\ConstantsAutoloadingClassDiscovery;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_keys;

/** @internal */
final class MatchedExceptionFormatterConstantsAutoloadingClassDiscovery implements ConstantsAutoloadingClassDiscovery
{
    /** @return array<class-string,true> */
    public function getClassNames(ContainerBuilder $container): array
    {
        $classNames = [];
        $taggedServiceIds = array_keys($container->findTaggedServiceIds(MatchedExceptionFormatter::class));

        foreach ($taggedServiceIds as $taggedServiceId) {
            $def = $container->getDefinition($taggedServiceId);

            /** @var class-string $className */
            $className = $def->getClass();

            $classNames[$className] = true;
        }

        return $classNames;
    }
}
