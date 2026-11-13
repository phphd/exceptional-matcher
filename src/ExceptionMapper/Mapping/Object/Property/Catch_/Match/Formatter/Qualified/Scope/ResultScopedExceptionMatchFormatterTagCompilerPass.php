<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope;

use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ExtensionCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\ResolveInstanceofConditionalsPass;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use WeakMap;

use function array_filter;
use function array_keys;
use function array_unique;
use function array_values;
use function preg_match;
use function preg_quote;
use function str_starts_with;

/** @internal */
final class ResultScopedExceptionMatchFormatterTagCompilerPass implements CompilerPassInterface
{
    /**
     * Must follow {@see ResolveInstanceofConditionalsPass} (100) and {@see ExtensionCompilerPass} (-1000),
     * yet precede {@see ServiceLocatorTagPass} (optimization stage).
     */
    public const PRIORITY = -2000;

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->groupFormatterIdsByFormatter($container) as $formatter => $formatterIds) {
            foreach ($this->resultTypesOf($formatterIds) as $resultType) {
                $this->copyTag($formatter, ExceptionMatchFormatter::class, ExceptionMatchFormatter::class.'<?,'.$resultType.'>');
            }
        }
    }

    /** @return WeakMap<Definition,non-empty-list<string>> own id, followed by generic alias ids */
    private function groupFormatterIdsByFormatter(ContainerBuilder $container): WeakMap
    {
        /** @var WeakMap<Definition,non-empty-list<string>> $formatterIdsByFormatter */
        $formatterIdsByFormatter = new WeakMap();

        foreach (array_keys($container->findTaggedServiceIds(ExceptionMatchFormatter::class)) as $formatterId) {
            $formatterIdsByFormatter[$container->getDefinition($formatterId)] = [$formatterId];
        }

        foreach (array_filter(array_keys($container->getAliases()), self::isGenericId(...)) as $genericAliasId) {
            $formatter = $container->findDefinition($genericAliasId);
            $formatterIds = $formatterIdsByFormatter[$formatter] ?? null;

            if (null !== $formatterIds) {
                $formatterIdsByFormatter[$formatter] = [...$formatterIds, $genericAliasId];
            }
        }

        return $formatterIdsByFormatter;
    }

    /**
     * @param list<string> $formatterIds
     *
     * @return list<string>
     */
    private function resultTypesOf(array $formatterIds): array
    {
        $resultTypes = [];

        foreach (array_filter($formatterIds, self::isGenericId(...)) as $genericId) {
            $resultType = self::resultTypeOf($genericId);

            if (null !== $resultType) {
                $resultTypes[] = $resultType;
            }
        }

        return array_values(array_unique($resultTypes));
    }

    private function copyTag(Definition $definition, string $sourceTag, string $targetTag): void
    {
        $definition->setTags([
            ...$definition->getTags(),
            $targetTag => [...$definition->getTag($targetTag), ...$definition->getTag($sourceTag)],
        ]);
    }

    private static function isGenericId(string $id): bool
    {
        return str_starts_with($id, ExceptionMatchFormatter::class.'<');
    }

    private static function resultTypeOf(string $genericId): ?string
    {
        $typeArgument = '([^<>,]+)';

        if (1 !== preg_match('/^'.preg_quote(ExceptionMatchFormatter::class, '/').'<'.$typeArgument.','.$typeArgument.'>$/', $genericId, $typeArguments)) {
            return null;
        }

        [, , $resultType] = $typeArguments;

        return $resultType;
    }
}
