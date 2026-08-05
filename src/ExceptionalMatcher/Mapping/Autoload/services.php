<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Autoload;

use Closure;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\closure;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service_closure;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services
        ->set(ConstantsClassLoader::class, ConstantsClassLoader::class)
        ->args([abstract_arg('Injected by '.ConstantsAutoloadingCompilerPass::class)]);
};
