<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Autoload;

use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\ObjectExceptionMappingPlanRegistry;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

use function array_keys;

/** @internal */
final class ConstantsAutoloadingCompilerPass implements CompilerPassInterface
{
    public const PRIORITY = 20;

    /** @var list<ConstantsClassDiscovery> */
    private readonly array $discovery;

    public function __construct()
    {
        $this->discovery = [
            new MatchConditionConstantsClassDiscovery(),
            new ExceptionFormatterConstantsClassDiscovery(),
        ];
    }

    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition(ObjectExceptionMappingPlanRegistry::class);

        $definition->replaceArgument(
            1,
            new ServiceClosureArgument(
                (new Definition())
                    ->setFactory([ConstantsClassLoader::class, 'loadClassNames'])
                    ->setArguments([$this->discoverAutoloadingNames($container)]),
            ),
        );
    }

    /** @return list<class-string> */
    private function discoverAutoloadingNames(ContainerBuilder $container): array
    {
        return array_keys(array_merge(...array_map(
            static fn (ConstantsClassDiscovery $discovery): array => $discovery->getClassNames($container),
            $this->discovery,
        )));
    }
}
