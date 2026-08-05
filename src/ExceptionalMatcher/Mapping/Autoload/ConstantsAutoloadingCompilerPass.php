<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Autoload;

use PhPhD\ExceptionalMatcher\Exception\Formatter\_Autoload\ExceptionFormatterConstantsAutoloadingClassDiscovery;
use PhPhD\ExceptionalMatcher\Mapping\Object\_Plan\_Registry\CompilingObjectExceptionMappingPlanRegistry;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\_Compiler\_Autoload\MatchConditionConstantsAutoloadingClassDiscovery;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_keys;
use function array_map;
use function array_merge;

/** @internal */
final class ConstantsAutoloadingCompilerPass implements CompilerPassInterface
{
    public const PRIORITY = 20;

    /** @internal the closure every {@see CompilingObjectExceptionMappingPlanRegistry} invokes before its first compilation */
    public const AUTOLOADER_ID = 'phd_exceptional_matcher.constants_autoloader';

    /** @var list<ConstantsAutoloadingClassDiscovery> */
    private readonly array $discovery;

    public function __construct()
    {
        $this->discovery = [
            new MatchConditionConstantsAutoloadingClassDiscovery(),
            new ExceptionFormatterConstantsAutoloadingClassDiscovery(),
        ];
    }

    public function process(ContainerBuilder $container): void
    {
        $container
            ->getDefinition(self::AUTOLOADER_ID)
            ->replaceArgument(0, $this->discoverLoadendClassNames($container))
        ;
    }

    /** @return list<class-string> */
    private function discoverLoadendClassNames(ContainerBuilder $container): array
    {
        return array_keys(array_merge(...array_map(
            static fn (ConstantsAutoloadingClassDiscovery $discovery): array => $discovery->getClassNames($container),
            $this->discovery,
        )));
    }
}
