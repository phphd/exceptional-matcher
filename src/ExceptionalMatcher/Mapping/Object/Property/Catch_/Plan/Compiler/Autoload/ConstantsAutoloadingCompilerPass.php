<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload;

use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Condition\Compiler\Autoload\MatchConditionConstantsAutoloadingClassDiscovery;
use PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Exception\Formatter\MatchedExceptionFormatterConstantsAutoloadingClassDiscovery;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use function array_keys;
use function array_map;
use function array_merge;

/** @internal */
final class ConstantsAutoloadingCompilerPass implements CompilerPassInterface
{
    public const PRIORITY = 20;

    public const AUTOLOADER_ID = 'phd_exceptional_matcher.constants_autoloader';

    /** @var list<ConstantsAutoloadingClassDiscovery> */
    private readonly array $discovery;

    public function __construct()
    {
        $this->discovery = [
            new MatchConditionConstantsAutoloadingClassDiscovery(),
            new MatchedExceptionFormatterConstantsAutoloadingClassDiscovery(),
        ];
    }

    public function process(ContainerBuilder $container): void
    {
        $container
            ->getDefinition(ConstantsClassLoader::class)
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
