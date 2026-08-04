<?php

declare(strict_types=1);

namespace PhPhD\ExceptionalMatcher\Mapping\Autoload;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;

return static function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    // Every plan registry invokes this through a service closure, so the constants behind `match:` / `format:`
    // are loaded once, lazily, right before the first plan is compiled.
    $services
        ->set(ConstantsAutoloadingCompilerPass::AUTOLOADER_ID, ConstantsClassLoader::class)
        ->factory([ConstantsClassLoader::class, 'loadClassNames'])
        ->args([
            abstract_arg('Injected by '.ConstantsAutoloadingCompilerPass::class),
        ])
    ;
};
