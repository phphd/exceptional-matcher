<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Qualified\Autoload;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Blueprint\Compiler\Autoload\ConstantsAutoloadingClassDiscovery;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Condition\Blueprint\MatchConditionCompiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_keys;

/** @internal */
final class QualifiedMatchConditionClassDiscovery implements ConstantsAutoloadingClassDiscovery
{
    /** @return array<class-string,true> */
    public function getClassNames(ContainerBuilder $container): array
    {
        $classNames = [];
        $taggedServiceIds = array_keys($container->findTaggedServiceIds(MatchConditionCompiler::class));

        foreach ($taggedServiceIds as $taggedServiceId) {
            $def = $container->getDefinition($taggedServiceId);

            /** @var class-string $className */
            $className = $def->getClass();

            $classNames[$className] = true;
        }

        return $classNames;
    }
}
