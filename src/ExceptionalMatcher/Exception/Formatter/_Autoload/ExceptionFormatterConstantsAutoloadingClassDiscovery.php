<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Exception\Formatter\_Autoload;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Mapping\Autoload\ConstantsAutoloadingClassDiscovery;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_keys;

/** @internal */
final class ExceptionFormatterConstantsAutoloadingClassDiscovery implements ConstantsAutoloadingClassDiscovery
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
