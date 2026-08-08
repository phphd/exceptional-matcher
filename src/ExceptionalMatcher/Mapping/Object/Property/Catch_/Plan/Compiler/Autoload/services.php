<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Object\Property\Catch_\Plan\Compiler\Autoload;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(ConstantsClassLoader::class, ConstantsClassLoader::class)
        ->args([abstract_arg('Injected by '.ConstantsAutoloadingCompilerPass::class)])
    ;
};
