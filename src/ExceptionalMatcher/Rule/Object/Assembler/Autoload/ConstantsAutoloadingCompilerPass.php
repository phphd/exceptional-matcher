<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Rule\Object\Assembler\Autoload;

use PhPhD\ExceptionalMatcher\Exception\Formatter\MatchedExceptionFormatter;
use PhPhD\ExceptionalMatcher\Rule\Assembler\MatchingRuleSetAssemblerService;
use PhPhD\ExceptionalMatcher\Rule\Object\Assembler\ObjectMatchingRuleSetAssembler;
use PhPhD\ExceptionalMatcher\Rule\Object\Property\Match\Condition\MatchConditionFactory;
use Symfony\Component\DependencyInjection\Argument\ServiceClosureArgument;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

use function array_keys;

/** @api */
final class ConstantsAutoloadingCompilerPass implements CompilerPassInterface
{
    public const PRIORITY = 20;

    public function process(ContainerBuilder $container): void
    {
        $classNamesSet = $this->getMatchConditionFactoryIds($container);
        $classNamesSet += $this->getExceptionFormatterIds($container);

        $definition = $container->getDefinition(MatchingRuleSetAssemblerService::class.'<'.ObjectMatchingRuleSetAssembler::class.'>');

        $definition->replaceArgument(
            1,
            new ServiceClosureArgument(
                (new Definition())
                    ->setFactory([ConstantsClassLoader::class, 'loadClassNames'])
                    ->setArguments([array_keys($classNamesSet)]),
            ),
        );
    }

    /** @return array<class-string,true> */
    private function getMatchConditionFactoryIds(ContainerBuilder $container): array
    {
        $classNames = [];
        $taggedServiceIds = array_keys($container->findTaggedServiceIds(MatchConditionFactory::class));

        foreach ($taggedServiceIds as $taggedServiceId) {
            $def = $container->getDefinition($taggedServiceId);

            /** @var class-string $className */
            $className = $def->getClass();

            $classNames[$className] = true;
        }

        return $classNames;
    }

    /** @return array<class-string,true> */
    private function getExceptionFormatterIds(ContainerBuilder $container): array
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
