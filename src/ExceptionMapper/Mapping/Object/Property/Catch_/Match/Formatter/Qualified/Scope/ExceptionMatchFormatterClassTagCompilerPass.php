<?php

declare(strict_types=1);

namespace PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\Qualified\Scope;

use Generator;
use PhPhD\ExceptionMapper\Mapping\Object\Property\Catch_\Match\Formatter\ExceptionMatchFormatter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ExtensionCompilerPass;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;

use function array_keys;

/** @internal */
final class ExceptionMatchFormatterClassTagCompilerPass implements CompilerPassInterface
{
    /**
     * After {@see ExtensionCompilerPass} (-1000): sees formatters registered by other extensions.
     * Before {@see ServiceLocatorTagPass} (optimization stage): scoped tags must exist before tagged locators are built.
     */
    public const PRIORITY = -2000;

    public function process(ContainerBuilder $container): void
    {
        foreach ($this->getFormatterDefinitions($container) as $formatter) {
            $formatter->addClassNameTags();
        }
    }

    /** @return Generator<ExceptionMatchFormatterDefinition> */
    private function getFormatterDefinitions(ContainerBuilder $container): Generator
    {
        foreach ($this->getFormattersServicesAliasIds($container) as $formatterServiceId => $aliasIds) {
            yield new ExceptionMatchFormatterDefinition($container->getDefinition($formatterServiceId), $formatterServiceId, $aliasIds);
        }
    }

    /** @return array<string,list<string>> [own id => alias ids] */
    private function getFormattersServicesAliasIds(ContainerBuilder $container): array
    {
        $aliasIdsByFormatterServiceId = [];

        foreach (array_keys($container->findTaggedServiceIds(ExceptionMatchFormatter::class)) as $formatterServiceId) {
            $aliasIdsByFormatterServiceId[$formatterServiceId] = [];
        }

        foreach (array_keys($container->getAliases()) as $aliasId) {
            $formatterServiceId = $this->unalias($container, $aliasId);

            if (!isset($aliasIdsByFormatterServiceId[$formatterServiceId])) {
                continue;
            }

            $aliasIdsByFormatterServiceId[$formatterServiceId][] = $aliasId;
        }

        return $aliasIdsByFormatterServiceId;
    }

    private function unalias(ContainerBuilder $container, string $aliasId): string
    {
        $aliasChain = [];
        $serviceId = $aliasId;

        while ($container->hasAlias($serviceId)) {
            if (isset($aliasChain[$serviceId])) {
                throw new ServiceCircularReferenceException($serviceId, [...array_keys($aliasChain), $serviceId]);
            }

            $aliasChain[$serviceId] = true;
            $serviceId = (string)$container->getAlias($serviceId);
        }

        return $serviceId;
    }
}
